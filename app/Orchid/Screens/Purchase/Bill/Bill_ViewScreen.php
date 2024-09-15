<?php

namespace App\Orchid\Screens\Purchase\Bill;

use App\Models\Product;
use App\Models\Purchase\Purchase;
use App\Models\Purchase\PurchaseDetail;
use App\Models\Purchase\PurchasePayment;
use App\Orchid\Screens\Purchase\TabMenuPurchase;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Label;
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
            'purchaseDetails' => $purchase->purchaseDetails()->get(),
            'purchase_model' => Purchase::where('id', $purchase->id)->get(),
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

            Button::make(__('Revoke Approval'))
                ->icon('bs.bag-check')
                ->confirm(__('You\'re about to revoke this purchase approval.'))
                // ->canSee($this->can('update'))
                ->canSee($this->purchase->status == Purchase::STATUS_APPROVED && $this->purchase->payment_status == PurchasePayment::STATUS_UNPAID)
                ->method('approvedRevoke'),

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
            new TabMenuPurchase($this->purchase),

            Layout::table('purchase_model', [
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
                TD::make('updated_by', 'Updated By')->alignRight()->render(fn($target) => $target->updatedBy->name ?? null),
            ]),

            Layout::rows([
                Label::make('purchase.note')
                    ->title('Note: ')
                    ->horizontal(),
                // TextArea::make('purchase.note')
                //     ->title('Note')
                //     ->rows(3)
                //     ->disabled()
                //     ->horizontal(),
                // 
            ]),

            Layout::table('purchaseDetails', [
                TD::make('id', '#')->width(10)->render(fn($target, object $loop) => $loop->iteration + (request('page') > 0 ? (request('page') - 1) * $target->getPerPage() : 0)),
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
                TD::make('quantity', 'Qty')->alignCenter()->width(50),
                TD::make('unit_price', 'Unit Price')->alignRight()->width(100),
                TD::make('sub_total', 'Total')->alignRight()->width(100),
                TD::make('quantity_return', 'QtyReturn')->alignCenter()->width(50),
                // 
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
                // 
            ]), //->title('Purchase Details'),

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
            DropDown::make()
                ->icon('three-dots-vertical')
                ->list([
                    Link::make(__('Add Return'))
                        ->icon('bs.plus-circle')
                        // ->canSee($this->can('view'))
                        ->canSee(($target->quantity > $target->quantity_return)
                                && ($this->purchase->status == Purchase::STATUS_APPROVED || $this->purchase->status == Purchase::STATUS_COMPLETED)
                        )
                        ->route('platform.purchases.returnbypurchasedetails.create', [$this->purchase, $target]),
                ]),
        ]);
    }

    public function approve(Purchase $purchase)
    {
        $purchaseDetails = PurchaseDetail::where('purchase_id', $purchase->id)->get();

        foreach ($purchaseDetails as $purchaseDetail) {
            updateStock($purchaseDetail->product_id, $purchaseDetail->quantity, 'purchase');
        }

        Purchase::findOrFail($purchase->id)
            ->update([
                'status' => Purchase::STATUS_APPROVED,
                'updated_by' => auth()->user()->id
            ]);

        // send notification to purchase creator and approver

        // 
        Toast::info(__('Purchase has been approved.'));

        return redirect()->route('platform.purchases.view', $this->purchase);
    }

    // hanya terpakai pada Purchase::STATUS_APPROVED dan Purchase::STATUS_UNPAID
    public function approvedRevoke(Purchase $purchase)
    {
        $purchaseDetails = PurchaseDetail::where('purchase_id', $purchase->id)->get();

        foreach ($purchaseDetails as $purchaseDetail) {
            // Product::where('id', $product->product_id)
            //         ->update(['quantity' => DB::raw('quantity+'.$product->quantity)]);
            updateStock($purchaseDetail->product_id, $purchaseDetail->quantity, 'purchaseRevoke');
        }

        Purchase::findOrFail($purchase->id)
            ->update([
                'status' => Purchase::STATUS_PENDING,
                'updated_by' => auth()->id(),
            ]);
        // send notification to purchase creator and approver

        // 
        Toast::warning(__('Purchase approval has been revoked!'));

        return redirect()->route('platform.purchases.view', $this->purchase);
    }
}
