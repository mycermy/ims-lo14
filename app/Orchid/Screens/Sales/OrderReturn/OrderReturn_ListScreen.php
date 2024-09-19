<?php

namespace App\Orchid\Screens\Sales\OrderReturn;

use App\Models\Sales\Order;
use App\Models\Sales\OrderPayment;
use App\Models\Sales\OrderReturnItem;
use App\Orchid\Screens\Sales\TabMenuOrder;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class OrderReturn_ListScreen extends Screen
{
    public ?Order $order = null;
    public $returns;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Order $order): iterable
    {
        // $returns = PurchaseReturn::where('purchase_id', $order->id)->get();
        // $returnItems = PurchaseReturnItem::whereIn('order_return_id', $returns->pluck('id'))->get();
        $returns = $order->returns;
        $returnItems = $returns ? OrderReturnItem::whereIn('order_return_id', $returns->pluck('id'))->get() : collect();

        return [
            'order' => $order,
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
        return 'Order: ' . $this->order->reference;
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
            //     ->canSee($this->showReturnMenu($this->order))
            //     ->route('platform.purchases.returns.create', $this->order),
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

            Layout::table('returnItems', [
                TD::make('id', '#')->render(fn($target, object $loop) => $loop->iteration + (request('page') > 0 ? (request('page') - 1) * $target->getPerPage() : 0)),
                TD::make('reference')->width(175)
                    ->render(
                        fn($target) => $target->orderReturn->reference
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
                ->route('platform.orders.returns.edit', [$this->order, $target]),

            Button::make(__(''))
                ->icon('bs.trash3')
                // ->type(Color::DANGER)
                ->confirm(__('Once the order is deleted, all of its resources and data will be permanently deleted. 
                    Before deleting your order, please download any data or information that you wish to retain.'))
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

    public function showReturnMenu($target)
    {
        $isApprovedOrCompleted = in_array($target->status, [Order::STATUS_APPROVED, Order::STATUS_COMPLETED]);
        $isAmountReturnLess = $this->order->total_amount_return < $this->order->total_amount;

        return $isApprovedOrCompleted && $isAmountReturnLess;
    }
}
