<?php

namespace App\Orchid\Screens\Purchase;

use App\Models\Contact;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\PurchasePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Menu;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class Bill_ViewScreen extends Screen
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
        return 'Bill: ' . $this->purchase->reference;
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make(__('Approve'))
                ->icon('bs.bag-check')
                ->confirm(__('You\'re about to approve this purchase.'))
                // ->canSee($this->can('update'))
                ->canSee($this->purchase->status == Purchase::STATUS_PENDING)
                ->method('approve'),

            Link::make(__('Back'))
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
        $harini = now()->toDateString();

        return [
            Layout::tabmenu([
                Menu::make('Purchase Details')
                    ->route('platform.purchases.view', $this->purchase),
    
                Menu::make('Payments')
                    ->route('platform.purchases.payments', $this->purchase),
            ]),

            Layout::rows([
                Group::make([
                    Input::make('purchase.reference')
                        ->title('Reference')
                        // ->required()
                        // ->value($refid)
                        ->disabled(),
                    //
                    DateTimer::make('purchase.date')
                        ->title('Date')
                        ->format('d M Y')
                        // ->required()
                        // ->value($harini)
                        ->disabled(),
                    // 
                    Relation::make('purchase.supplier_id')
                        ->title('Supplier')
                        ->fromModel(Contact::class, 'name')
                        // ->applyScope('supplier')
                        // ->searchColumns('name', 'phone', 'email')
                        // ->chunk(10)
                        ->disabled(),
                    // 
                ])->fullWidth(),
                //
                TextArea::make('purchase.note')
                    ->title('Note (If Needed)')
                    ->rows(3)
                    ->disabled()
                    ->horizontal(),
                // 
            ]),

            Layout::table('purchaseDetail', [
                TD::make('id', '#')->width(10)->render(fn ($target, object $loop) => $loop->iteration + (request('page') > 0 ? (request('page') - 1) * $target->getPerPage() : 0)),
                TD::make('product_id', 'Code')
                    // ->render(fn ($target) => $target->product->code ?? null),
                    ->render(
                        function ($target) {
                            if ($target->product->code) {
                                return Link::make($target->product->code)
                                    ->route('platform.product.hist', $target->product->id);
                            } else {
                                return null;
                            }
                        }
                    ),
                TD::make('product_id', 'Product')->render(fn ($target) => $target->product->name ?? null),
                TD::make('quantity', 'Qty')->alignCenter(),
                TD::make('unit_price', 'Unit Price')->alignRight(),
                TD::make('sub_total', 'Total')->alignRight(),
                ]),//->title('Purchase Details'),

        ];
    }

    public function approve(Purchase $purchase)
    {
        $purchaseDetails = PurchaseDetail::where('purchase_id', $purchase->id)->get();

        foreach ($purchaseDetails as $purchaseDetail) {
            $this->updateStock($purchaseDetail->product_id, $purchaseDetail->quantity, 'add');
        }

        Purchase::findOrFail($purchase->id)
            ->update([
                'status' => Purchase::STATUS_APPROVED,
                'updated_by' => auth()->user()->id
            ]);

        // send notification to purchase creator and approver

        // 
        Toast::info(__('Purchase has been approved.'));

        return redirect()->route('platform.purchases');
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
