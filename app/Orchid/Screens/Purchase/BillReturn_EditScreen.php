<?php

namespace App\Orchid\Screens\Purchase;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Rules\ValueNotExceed;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class BillReturn_EditScreen extends Screen
{
    public ?Purchase $purchase = null;
    public ?PurchaseReturn $return = null;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Purchase $purchase, PurchaseReturn $return): iterable
    {
        return [
            'purchase' => $purchase,
            'return' => $return,
            'returnItem' => $purchase->purchaseDetails()->get(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Bill: ' . $this->purchase->reference . ' >> ' .
            ($this->return->exists
                ? 'Edit Return: ' . $this->return->reference
                : 'New Purchase Return');
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
                ->canSee(!$this->return->exists)
                ->method('store'),

            Link::make(__('Cancel'))
                ->icon('bs.x-circle')
                ->route('platform.purchases.returns', $this->purchase),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        $number = PurchaseReturn::max('id') + 1;
        $refid = make_reference_id('PRRN', $number);
        $harini = now()->toDateString(); //dd($harini);

        return [
            Layout::rows([
                TextArea::make('return.reason')
                    ->title('Reason For Return')
                    ->required()
                    ->rows(3)
                    ->max(1000),
                // 
                Input::make('return.reference')
                    ->value($refid)
                    ->type('hidden'),
                //
                Input::make('return.purchase_id')
                    ->value($this->purchase->id)
                    ->type('hidden'),
                //
            ]),
            // 
            Layout::columns([
                Layout::rows([
                    Matrix::make('returnItem')
                        ->title('Return Items')
                        ->removeableRows(false)
                        ->columns([
                            'Purchase Item Ref' => 'id',
                            'Product' => 'product_id',
                            'quantity',
                            // 'sub_total',
                        ])
                        ->fields([
                            // 'id' => Input::make('id')->readonly()->type('hidden'),
                            'product_id' => Select::make('product_id')->fromModel(Product::class, 'name')->disabled(),
                            'quantity' => Input::make('quantity')->type('number')->required(),
                            // 'sub_total' => Input::make('sub_total')->readonly(),
                        ]),
                    //
                ]),

            ]),
        ];
    }

    public function store(Request $request, PurchaseReturn $purchaseReturn)
    {
        dd($request->input('return'), $request->input('returnItem'));

        $purchase = Purchase::findOrFail($request->input('return.purchase_id'));
        $items = $request->input('returnItem'); // Array of items with 'purchase_item_id', 'quantity', 'amount'

        // Validate return quantities
        foreach ($items as $returnItem) {
            $purchaseItem = PurchaseDetail::findOrFail($returnItem['purchase_detail_id']);
            if ($returnItem['quantity'] > ($purchaseItem->quantity - $purchaseItem->quantity_return)) {
                return response()->json(['error' => 'Return quantity exceeds purchased quantity for item ID: ' . $returnItem['purchase_item_id']], 400);
            }
            $subTotal = $returnItem['quantity'] * $purchaseItem->unit_price;
        }

        $request->validate([
            'return.reference' => 'required|string|max:255',
            'returnItem.quantity' => [
                'required',
                'numeric',
                new ValueNotExceed($request->input('inventory.quantity'), 'The sales quantity should not exceed the inventory quantity.')
            ],
            'return.purchase_id' => 'required',
        ]);


        $totalAmount = array_sum(array_column($items, 'amount'));

        $purchaseReturn = PurchaseReturn::create([
            'purchase_id' => $purchase->id,
            'total_amount' => $totalAmount,
            'reason' => $request->input('reason'),
        ]);

        foreach ($items as $item) {
            PurchaseReturnItem::create([
                'purchase_return_id' => $purchaseReturn->id,
                'purchase_item_id' => $item['purchase_item_id'],
                'quantity' => $item['quantity'],
                'amount' => $item['amount'],
            ]);
        }

        // Update purchase amounts
        $purchase->update([
            'paid_amount' => $purchase->paid_amount - $totalAmount,
            'due_amount' => $purchase->due_amount + $totalAmount,
        ]);

        return response()->json(['message' => 'Purchase return processed successfully.']);
    }
}
