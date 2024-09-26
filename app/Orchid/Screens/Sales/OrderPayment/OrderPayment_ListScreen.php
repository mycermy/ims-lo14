<?php

namespace App\Orchid\Screens\Sales\OrderPayment;

use App\Models\Sales\Order;
use App\Models\Sales\OrderPayment;
use App\Orchid\Screens\Sales\TabMenuOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class OrderPayment_ListScreen extends Screen
{
    public ?Order $order = null;
    public $orderPayment;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Order $order): iterable
    {
        return [
            'order' => $order,
            'orderPayment' => $order->orderPayments()->get(),
            'order_model' => Order::where('id', $order->id)->get(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Order: ' . $this->order->reference;
    }

    /**
     * Display header description.
     */
    // public function description(): ?string
    // {
    //     return 'Payments done for this order.';
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
                ->canSee($this->showPaymentMenu($this->order))
                ->route('platform.orders.payments.create', $this->order),
            // 
            Link::make(__('Back'))
                ->icon('bs.x-circle')
                ->route('platform.orders'),
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
            new TabMenuOrder($this->order),

            Layout::table('order_model', [
                TD::make('total_amount', 'Total Amount')->alignRight(),
                TD::make('total_amount_return', 'Total Amount Return')->alignRight(),
                TD::make('paid_amount', 'Paid Amount')->alignRight(),
                TD::make('due_amount', 'Due Amount')->alignRight(),
                TD::make('payment_status', 'Payment Status')->alignCenter()
                    ->render(function ($target) {
                        if ($target->payment_status == OrderPayment::STATUS_PAID) {
                            $button = 'text-bg-success text-white';
                        } elseif ($target->payment_status == OrderPayment::PAYMENT_REFUND) {
                            $button = 'text-bg-warning';
                        } else {
                            $button = 'text-bg-danger';
                        }
                        //
                        return Link::make($target->payment_status)->class($button . ' badge text-uppercase');
                    }),
            ]),

            Layout::table('orderPayment', [
                // TD::make('id', '#')->render(fn($target, object $loop) => $loop->iteration + (getPage() - 1) * $target->getPerPage()),
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
            ]), //->title('Order Payments'),
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
                ->route('platform.orders.payments.edit', [$this->order, $target]),

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
        // rollback paid amount in order table
        $orderPayment = OrderPayment::findOrFail($request->get('id'));
        $order = $orderPayment->order;

        $paid_amount = $order->paid_amount - $orderPayment->amount;

        $due_amount = $order->due_amount + $orderPayment->amount;

        $payment_status = match (true) {
            $due_amount == $order->total_amount => OrderPayment::STATUS_UNPAID,
            $due_amount > 0 => OrderPayment::STATUS_PARTIALLY_PAID,
            $due_amount < 0 => OrderPayment::STATUS_OVERPAID,
            default => OrderPayment::STATUS_PAID,
        };

        $order->update([
            'paid_amount' => $paid_amount,
            'due_amount' => $due_amount,
            'payment_status' => $payment_status,
            'status' => $payment_status != OrderPayment::STATUS_PAID ? Order::STATUS_APPROVED : $order->status,
        ]);

        // parent
        $orderPayment->delete();

        Toast::info(__('Order Payment was deleted.'));
    }

    public function showPaymentMenu($target)
    {
        if (
            $target->status == Order::STATUS_APPROVED &&
            !in_array($target->payment_status, [OrderPayment::STATUS_PAID, OrderPayment::STATUS_OVERPAID])
        ) {
            return true;
        }
        return false;
    }
}
