<?php

namespace App\Orchid\Screens\Sales\Order;

use App\Models\Sales\Order;
use App\Models\Sales\OrderPayment;
use App\Orchid\Screens\Sales\TabMenuOrder;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class Order_ViewScreen extends Screen
{
    public ?Order $order = null;
    public $orderDetail;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Order $order): iterable
    {
        return [
            'order' => $order,
            'orderDetail' => $order->orderItems()->get(),
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
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
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
                TD::make('status')->alignCenter()
                    ->render(
                        function ($target) {
                            $class = ($target->status == Order::STATUS_APPROVED
                                || ($target->status == Order::STATUS_COMPLETED && $target->payment_status == OrderPayment::STATUS_PAID))
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
                TD::make('customer_name', 'Customer'),
                TD::make('updated_by', 'Updated By')->alignRight()->render(fn($target) => $target->updatedBy->name ?? null),
            ]),

            Layout::rows([
                Label::make('order.note')
                    ->title('Note: ')
                    ->horizontal(),
            ]),

            Layout::table('orderDetail', [
                TD::make('id', '#')->render(fn($target, object $loop) => $loop->iteration + (getPage() - 1) * $target->getPerPage()),
                TD::make('product_id', 'Code')
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
        return Group::make([
            DropDown::make()
                ->icon('three-dots-vertical')
                ->list([
                    Link::make(__('Add Return'))
                        ->icon('bs.plus-circle')
                        // ->canSee($this->can('view'))
                        ->canSee(($target->quantity > $target->quantity_return)
                                && ($this->order->status == Order::STATUS_APPROVED || $this->order->status == Order::STATUS_COMPLETED)
                        )
                        ->route('platform.orders.returnbyorderitem.create', [$this->order, $target]),
                ]),
        ]);
    }
}
