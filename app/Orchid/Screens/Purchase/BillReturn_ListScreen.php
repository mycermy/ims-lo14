<?php

namespace App\Orchid\Screens\Purchase;

use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Menu;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class BillReturn_ListScreen extends Screen
{
    public ?Purchase $purchase = null;
    public $returns;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Purchase $purchase): iterable
    {
        // $returns = PurchaseReturn::where('purchase_id', $purchase->id)->get();
        // $returnItems = PurchaseReturnItem::whereIn('purchase_return_id', $returns->pluck('id'))->get();
        $returns = $purchase->returns;
        $returnItems = $returns ? PurchaseReturnItem::whereIn('purchase_return_id', $returns->pluck('id'))->get() : collect();

        return [
            'purchase' => $purchase,
            'returns' => $returns,
            'returnItems' => $returnItems,
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
     * Display header description.
     */
    // public function description(): ?string
    // {
    //     return 'Payments done for this bill.';
    // }

    /**
     * The permissions required to access this screen.
     */
    // public function permission(): ?iterable
    // {
    //     return [
    //         'platform.systems.roles',
    //     ];
    // }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            // Link::make(__('Add Return'))
            //     ->icon('bs.wallet2')
            //     ->canSee($this->showReturnMenu($this->purchase))
            //     ->route('platform.purchases.returns.create', $this->purchase),
            // 
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
        return [
            new TabMenuPurchase($this->purchase),

            Layout::table('returnItems', [
                TD::make('id', '#')->width(10)->render(fn($target, object $loop) => $loop->iteration + (request('page') > 0 ? (request('page') - 1) * $target->getPerPage() : 0)),
                TD::make('reference')->width(150)
                    ->render(
                        fn($target) => $target->purchaseReturn->reference
                    ),
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
                TD::make('product_id', 'Product')->render(fn($target) => $target->product->name ?? null),
                TD::make('quantity')->alignCenter()->width(50),
                TD::make('sub_total')->alignRight()->width(150),

                // TD::make('actions')->alignCenter()
                //     ->canSee(Auth::user()->hasAnyAccess(['platform.systems.editor', 'platform.items.editor']))
                //     ->width('120px')
                //     ->render(
                //         fn ($target) =>
                //         $this->getTableActions($target)
                //             ->alignCenter()
                //             // ->autoWidth()
                //             ->render()
                //     ),
            ]), //->title('Purchase Payments'),
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
                // ->type(Color::PRIMARY)
                ->route('platform.purchases.returns.edit', [$this->purchase, $target]),

            Button::make(__(''))
                ->icon('bs.trash3')
                // ->type(Color::DANGER)
                ->confirm(__('Once the product is deleted, all of its resources and data will be permanently deleted. 
                    Before deleting your product, please download any data or information that you wish to retain.'))
                // ->canSee(!$target->trashed())
                ->method('removePayment', [
                    'id' => $target->id,
                ]),
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

    public function showReturnMenu($target)
    {
        $isApprovedOrCompleted = in_array($target->status, [Purchase::STATUS_APPROVED, Purchase::STATUS_COMPLETED]);
        $isAmountReturnLess = $this->purchase->total_amount_return < $this->purchase->total_amount;

        return $isApprovedOrCompleted && $isAmountReturnLess;
    }
}
