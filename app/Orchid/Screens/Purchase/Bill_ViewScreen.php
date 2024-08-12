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
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
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
    public $purchasePayment;
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
            'purchasePayment' => $purchase->purchasePayments()->get(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->purchase->exists ? 'View ' . $this->purchase->reference : 'New Purchase Bill';
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

            Link::make(__('Add Payment'))
                ->icon('bs.wallet2')
                ->canSee($this->showPaymentMenu($this->purchase))
                ->route('platform.purchases.payments.create', $this->purchase),
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

            Layout::table('purchasePayment', [
                TD::make('id', '#')->width(10)->render(fn ($target, object $loop) => $loop->iteration + (request('page') > 0 ? (request('page') - 1) * $target->getPerPage() : 0)),
                TD::make('date'),
                TD::make('reference'),
                TD::make('payment_method'),
                TD::make('amount')->alignRight(),
                TD::make('note'),

                TD::make('actions')->alignCenter()
                    ->canSee(Auth::user()->hasAnyAccess(['platform.systems.editor', 'platform.items.editor']))
                    ->width('120px')
                    ->render(
                        fn ($target) =>
                        $this->getTableActions($target)
                            ->alignCenter()
                            // ->autoWidth()
                            ->render()
                    ),
            ])->title('Purchase Payments'),
        ];
    }

    /**
     * @param Model $model
     *
     * @return Group
     */
    private function getTableActions($target): Group
    {
        return Group::make([
            Link::make(__(''))
                ->icon('pencil')
                ->route('platform.purchases.payments.edit', [$this->purchase, $target]),

            Button::make(__(''))
                ->icon('bs.trash3')
                ->confirm(__('Once the product is deleted, all of its resources and data will be permanently deleted. 
                    Before deleting your product, please download any data or information that you wish to retain.'))
                // ->canSee(!$target->trashed())
                ->method('removePayment', [
                    'id' => $target->id,
                ]),
        ]);
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

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removePayment(Request $request)
    {
        // rollback paid amount in purchase table
        $purchasePayment = PurchasePayment::findOrFail($request->get('id'));
        $purchase = $purchasePayment->purchase;

        $paid_amount = $purchase->paid_amount - $purchasePayment->amount;

        $due_amount = $purchase->due_amount + $purchasePayment->amount;

        $payment_status = match (true) {
            $due_amount == $purchase->total_amount => PurchasePayment::STATUS_UNPAID,
            $due_amount > 0 => PurchasePayment::STATUS_PARTIALLY_PAID,
            $due_amount < 0 => PurchasePayment::STATUS_OVERPAID,
            default => PurchasePayment::STATUS_PAID,
        };

        $purchase->update([
            'paid_amount' => $paid_amount,
            'due_amount' => $due_amount,
            'payment_status' => $payment_status,
            'status' => $payment_status != PurchasePayment::STATUS_PAID ? Purchase::STATUS_APPROVED : $purchase->status,
        ]);

        // parent
        $purchasePayment->delete();

        Toast::info(__('Purchase Payment was deleted.'));
    }

    public function showPaymentMenu($target)
    {
        if ($target->status == Purchase::STATUS_APPROVED && $target->payment_status != PurchasePayment::STATUS_PAID) {
            return true;
        }
        return false;
    }
}
