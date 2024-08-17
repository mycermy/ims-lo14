<?php

namespace App\Orchid\Screens\Purchase;

use App\Models\Purchase;
use App\Models\PurchasePayment;
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

class BillPayment_ListScreen extends Screen
{
    public ?Purchase $purchase = null;
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
            'purchasePayment' => $purchase->purchasePayments()->get(),
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
            Link::make(__('Add Payment'))
                ->icon('bs.wallet2')
                ->canSee($this->showPaymentMenu($this->purchase))
                ->route('platform.purchases.payments.create', $this->purchase),
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
            // Layout::tabmenu([
            //     Menu::make('Purchase Details')
            //         ->route('platform.purchases.view', $this->purchase),

            //     Menu::make('Payments')
            //         ->route('platform.purchases.payments', $this->purchase),
            // ]),

            new TabMenuPurchase($this->purchase),

            Layout::table('purchase_model', [
                TD::make('total_amount')->alignRight(),
                TD::make('total_amount_return')->alignRight(),
                TD::make('paid_amount')->alignRight(),
                TD::make('due_amount')->alignRight(),
                TD::make('payment_status')->alignCenter()
                    ->render(function ($target) {
                        if ($target->payment_status == PurchasePayment::STATUS_PAID) {
                            $button = 'text-bg-success text-white';
                        } elseif ($target->payment_status == PurchasePayment::PAYMENT_REFUND) {
                            $button = 'text-bg-warning';
                        } else {
                            $button = 'text-bg-danger';
                        }
                        //
                        return Link::make($target->payment_status)->class($button . ' badge text-uppercase');
                    }),
            ]),

            Layout::table('purchasePayment', [
                TD::make('id', '#')->render(fn($target, object $loop) => $loop->iteration + (request('page') > 0 ? (request('page') - 1) * $target->getPerPage() : 0)),
                TD::make('date')->width(150),
                TD::make('reference')->width(150),
                TD::make('note'),
                TD::make('payment_method', 'Payment Method')->alignCenter()->width(150),
                TD::make('amount')->alignRight()->width(100),
                TD::make('updated_by', 'Updated By')->width(150)->render(fn($target) => $target->updatedBy->name ?? null),

                TD::make('actions')->alignCenter()
                    ->canSee(Auth::user()->hasAnyAccess(['platform.systems.editor', 'platform.items.editor']))
                    ->width('120px')
                    ->render(
                        fn($target) =>
                        $this->getTableActions($target)
                            ->alignCenter()
                            // ->autoWidth()
                            ->render()
                    ),
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
                ->route('platform.purchases.payments.edit', [$this->purchase, $target]),

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

    public function showPaymentMenu($target)
    {
        if (
            $target->status == Purchase::STATUS_APPROVED &&
            !in_array($target->payment_status, [PurchasePayment::STATUS_PAID, PurchasePayment::STATUS_OVERPAID])
        ) {
            return true;
        }
        return false;
    }
}
