<?php

namespace App\Orchid\Screens\Purchase;

use App\Models\Contact;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class Bill_EditScreen extends Screen
{
    public ?Purchase $purchase = null;
    public $purchaseDetail;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Purchase $purchase): iterable
    {
        return [
            'purchase' => $purchase,
            'purchaseDetail' => $purchase->purchaseDetails()->get(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->purchase->exists ? 'Edit ' . $this->purchase->reference : 'New Purchase Bill';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make(__('Save'))
                ->icon('bs.check-circle')
                ->canSee(!$this->purchase->exists)
                ->method('store'),

            Button::make(__('Update'))
                ->icon('bs.check-circle')
                ->canSee($this->purchase->exists)
                ->method('store'),

            Link::make(__('Cancel'))
                ->icon('bs.x-circle')
                ->route('platform.purchases'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        $number = Purchase::max('id') + 1;
        $refid = make_reference_id('PR', $number);
        // $harini = now()->toDateString(); //dd($harini);
        $harini = now()->format('d M Y'); //dd($harini);

        return [
            Layout::rows([
                Group::make([
                    Input::make('purchase.reference')
                        ->title('Reference')
                        ->required()
                        ->value($refid)
                        ->disabled()
                        ->horizontal(),
                    //
                    DateTimer::make('purchase.date')
                        ->title('Date')
                        ->format('d M Y')
                        ->required()
                        ->value($harini)
                        ->allowInput()
                        ->horizontal(),
                    //
                    Relation::make('purchase.supplier_id')
                        ->title('Supplier')
                        ->fromModel(Contact::class, 'name')
                        ->applyScope('supplier')
                        ->searchColumns('name', 'phone', 'email')
                        ->chunk(10)
                        ->required()
                        ->horizontal(),
                ])->fullWidth(),
                //
                TextArea::make('purchase.note')
                    ->title('Note (If Needed)')
                    ->rows(3)
                    ->horizontal(),
                // 
                Matrix::make('purchaseDetail')
                    ->title('Purchase Details')
                    ->removeableRows(false)
                    ->columns(['id', 'Product' => 'product_id', 'quantity', 'Unit Price' => 'unit_price', 'sub_total'])
                    ->fields([
                        'id' => Input::make('id')->readonly()->type('hidden'),
                        'product_id' => Relation::make('product_id')->fromModel(Product::class, 'name')->readonly()->searchColumns('name', 'code', 'part_number')->chunk(10)->required(),
                        'quantity' => Input::make('quantity')->type('number')->required(),
                        'unit_price' => Input::make('unit_price')->required(),
                        'sub_total' => Input::make('sub_total')->readonly(),
                    ]),
                //
                Select::make('purchase.status')
                    ->title('Purchase Status')
                    ->options([
                        Purchase::STATUS_PENDING => Purchase::STATUS_PENDING,
                        Purchase::STATUS_APPROVED => Purchase::STATUS_APPROVED,
                    ])
                    // ->empty('No select')
                    ->horizontal(),
            ]),
        ];
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Purchase $purchase)
    {
        // kalo edit
        if ($this->purchase->exists) {
            $this->removeOldPurchaseDetails($purchase);
        }
        // 
        $supplier = Supplier::findOrFail($request->input('purchase.supplier_id'));

        $purchase->fill($request->get('purchase'));
        $purchase->fill([
            'date' => Carbon::parse($request->input('purchase.date'))->toDate(),
            'supplier_name' => $supplier->name,
            'updated_by' => auth()->id(),
        ]);
        $purchase->save();

        $totalAmount = 0;

        $purchaseDetails = $request->get('purchaseDetail');
        foreach ($purchaseDetails as $purchaseDetail) {
            $subTotal = $purchaseDetail['quantity'] * $purchaseDetail['unit_price'];

            // Create a new PurchaseDetail instance
            $newPurchaseDetail = new PurchaseDetail($purchaseDetail);
            $newPurchaseDetail->sub_total = $subTotal; // Set the sub_total attribute

            // Associate the new PurchaseDetail with the $purchase model
            $purchase->purchaseDetails()->save($newPurchaseDetail);

            // Update stock quantity in the product
            if ($request->input('purchase.status') == Purchase::STATUS_APPROVED) {
                $this->updateStock($purchaseDetail['product_id'], $purchaseDetail['quantity'], 'add');
            }
            //
            $totalAmount += $subTotal;
        }

        $purchase->fill(['total_amount' => $totalAmount, 'due_amount' => $totalAmount])->save();

        Toast::info(__('Purchase bill was saved.'));

        return redirect()->route('platform.purchases');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeOldPurchaseDetails(Purchase $purchase)
    {
        $oldPurchaseStatus = $purchase->status ?? null;

        $oldPurchaseDetails = PurchaseDetail::where('purchase_id', $purchase->id)->get();

        foreach ($oldPurchaseDetails as $oldPurchaseDetail) {
            // Update stock quantity in the product -> reverse
            if ($oldPurchaseStatus == Purchase::STATUS_APPROVED) {
                $this->updateStock($oldPurchaseDetail->product_id, $oldPurchaseDetail->quantity, 'sub');
            }

            $oldPurchaseDetail->delete();
        }
    }

    public function approve(Purchase $purchase)
    {
        $purchaseDetails = PurchaseDetail::where('purchase_id', $purchase->id)->get();

        foreach ($purchaseDetails as $purchaseDetail) {
            // Product::where('id', $product->product_id)
            //         ->update(['quantity' => DB::raw('quantity+'.$product->quantity)]);
            $this->updateStock($purchaseDetail->product_id, $purchaseDetail->quantity, 'add');
        }

        Purchase::findOrFail($purchase->id)
            ->update([
                'status' => Purchase::STATUS_APPROVED,
                'updated_by' => auth()->user()->id
            ]);

        return redirect()
            ->back()
            ->with('success', 'Purchase has been approved!');
    }

    public function updateStock($productID, $purchaseQty, $type)
    {
        $product = Product::findOrFail($productID);
        $updateQty = 0;

        if ($type == 'add') {
            $updateQty = $product->quantity + $purchaseQty;
        } else if ($type == 'sub') {
            $updateQty = $product->quantity - $purchaseQty;
        }

        // Update stock quantity in the product
        $product->update([
            'quantity' => $updateQty
        ]);
    }
}
