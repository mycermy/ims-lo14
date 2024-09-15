<?php

namespace App\Orchid\Screens\Purchase\Bill;

use App\Models\Product;
use Orchid\Screen\TD;
use App\Models\Purchase\Purchase;
use App\Models\Purchase\PurchasePayment;
use Orchid\Screen\Screen;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Layout;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Fields\Group;
use Orchid\Support\Facades\Toast;

class Bill_ListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'model' => Purchase::filters()->orderByDesc('date')->orderByDesc('id')->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Bills Listing';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Add'))
                ->icon('bs.plus-circle')
                ->route('platform.purchases.create'),
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
            Layout::table('model', [
                TD::make('status')->alignCenter()
                    ->render(
                        function ($target) {
                            $class = ($target->status == Purchase::STATUS_APPROVED
                                || ($target->status == Purchase::STATUS_COMPLETED && $target->payment_status == PurchasePayment::STATUS_PAID))
                                ? 'text-bg-success text-white'
                                : 'text-bg-danger';
                            return Link::make($target->status)
                                ->class($class . ' badge text-uppercase')
                                ->route('platform.purchases.view', $target);
                        }
                    ),
                TD::make('date'),
                TD::make('reference')
                    ->render(
                        fn($target) =>
                        Link::make($target->reference)
                            ->route('platform.purchases.view', $target)
                    ),
                TD::make('supplier_name', 'Supplier'),
                TD::make('total_amount')->alignRight(),
                TD::make('paid_amount')->alignRight(),
                TD::make('due_amount')->alignRight(),
                TD::make('payment_status', 'Payment Status')->alignCenter()
                    ->render(function ($target) {
                        if ($target->payment_status == PurchasePayment::STATUS_PAID) {
                            $button = 'text-bg-success text-white';
                            // } else if ($target->created_at <= now() && $target->due_at >= now()) {
                            //     $button = 'btn btn-warning';
                        } elseif ($target->payment_status == PurchasePayment::PAYMENT_REFUND) {
                            $button = 'text-bg-warning';
                        } else {
                            $button = 'text-bg-danger';
                        }
                        //
                        return Link::make($target->payment_status)
                            ->route('platform.purchases.payments', $target)
                            ->class($button . ' badge text-uppercase');
                    }),

                TD::make('actions')->alignCenter()
                    ->canSee(Auth::user()->hasAnyAccess(['platform.systems.editor', 'platform.items.editor']))
                    ->width('10px')
                    ->render(
                        fn($target) =>
                        $this->getTableActions($target)
                            ->alignCenter()
                            ->autoWidth()
                            ->render()
                    ),
            ]),
        ];
    }

    /**
     * @param Model $model
     *
     * @return Group
     */
    private function getTableActions($target): Group
    {
        $allowEdit = !($target->status == Purchase::STATUS_APPROVED || $target->status == Purchase::STATUS_COMPLETED);

        return Group::make([

            DropDown::make()
                ->icon('three-dots-vertical')
                ->list([
                    Link::make(__('View'))
                        ->icon('eye')
                        // ->canSee($this->can('view'))
                        ->route('platform.purchases.view', $target),

                    Link::make(__('Add Payment'))
                        ->icon('bs.plus-circle')
                        // ->canSee($this->can('view'))
                        ->canSee($this->showPaymentMenu($target))
                        ->route('platform.purchases.payments.create', $target),

                    Link::make(__('Edit'))
                        ->icon('pencil')
                        ->canSee($allowEdit)
                        ->route('platform.purchases.edit', $target),

                    Button::make(__('Approve'))
                        ->icon('bag-check')
                        ->confirm(__('You\'re about to approve this purchase.'))
                        // ->canSee($this->can('update'))
                        ->canSee($target->status == Purchase::STATUS_PENDING)
                        ->method('approve', [
                            'id' => $target->id,
                        ]),

                    Button::make(__('Delete'))
                        ->icon('bs.trash3')
                        ->confirm(__('Once the product is deleted, all of its resources and data will be permanently deleted. 
                            Before deleting your product, please download any data or information that you wish to retain.'))
                        // ->canSee(!$target->trashed())
                        ->method('remove', [
                            'id' => $target->id,
                        ]),
                ]),
        ]);
    }

    public function approve(Request $request)
    {
        $purchase = Purchase::findOrFail($request->get('id'));

        $purchaseDetails = $purchase->purchaseDetails()->get();

        foreach ($purchaseDetails as $purchaseDetail) {
            $this->updateStock($purchaseDetail->product_id, $purchaseDetail->quantity, 'add');
        }

        $purchase->update([
            'status' => Purchase::STATUS_APPROVED,
            'updated_by' => auth()->user()->id
        ]);

        // send notification to purchase creator and approver

        // 
        Toast::info(__('Purchase has been approved.'));
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
    public function remove(Request $request)
    {
        $purchase = Purchase::findOrFail($request->get('id'));

        // child
        foreach ($purchase->purchaseDetails as $existingPurchaseDetail) {
            // rollback stock qty in product table
            $this->updateStock($existingPurchaseDetail['product_id'], $existingPurchaseDetail['quantity'], 'sub');
        }

        // parent
        $purchase->delete();

        Toast::info(__('Purchase was deleted.'));
    }

    public function showPaymentMenu($target)
    {
        if ($target->status == Purchase::STATUS_APPROVED && $target->payment_status != PurchasePayment::STATUS_PAID) {
            return true;
        }
        return false;
    }
    // 

}
