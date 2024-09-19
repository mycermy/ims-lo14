<?php

namespace App\Orchid\Screens\Sales\Order;

use Carbon\Carbon;
use App\Models\Product;
use App\Models\Customer;
use Orchid\Screen\Screen;
use App\Models\Sales\Order;
use Illuminate\Http\Request;
use App\Models\Sales\OrderItem;
use App\Orchid\Layouts\OrderListener;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;

class Order_EditScreen extends Screen
{
    public ?Order $order = null;
    public $orderItem;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Order $order): iterable
    {
        return [
            'order' => $order,
            'orderItems' => $order->orderItems()->get(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->order->exists ? 'Edit ' . $this->order->reference : 'New Order';
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
                ->canSee(!$this->order->exists)
                ->method('store'),

            Link::make(__('Cancel'))
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
            OrderListener::class,
        ];
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Order $order)
    {
        // kalo edit
        if ($this->order->exists) {
            $this->removeOldOrderDetails($order);
        }
        // 
        $customer = Customer::findOrFail($request->input('order.customer_id'));

        $order->fill($request->get('order'));
        $order->fill([
            'date' => Carbon::parse($request->input('order.date'))->toDate(),
            'customer_name' => $customer->name,
            'updated_by' => auth()->id(),
        ]);
        $order->save();

        $totalAmount = 0;

        $orderDetails = $request->get('orderItems');
        foreach ($orderDetails as $orderItem) {
            $product = Product::findOrFail($orderItem['product_id']);
            $subTotal = $orderItem['quantity'] * $product->sell_price;

            // Create a new OrderDetail instance
            $newOrderDetail = new OrderItem($orderItem);
            $newOrderDetail->unit_price = $product->sell_price; // Set the unit_price attribute
            $newOrderDetail->sub_total = $subTotal; // Set the sub_total attribute

            // Associate the new OrderDetail with the $order model
            $order->orderItems()->save($newOrderDetail);

            // Update stock quantity in the product
            if ($request->input('order.status') == Order::STATUS_APPROVED) {
                updateStock($orderItem['product_id'], $orderItem['quantity'], 'sub');
            }
            //
            $totalAmount += $subTotal;
        }

        $order->fill(['total_amount' => $totalAmount, 'due_amount' => $totalAmount])->save();

        Toast::info(__('Order was saved.'));

        return redirect()->route('platform.orders.view', $order);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeOldOrderDetails(Order $order)
    {
        $oldOrderStatus = $order->status ?? null;

        $oldOrderDetails = OrderItem::where('order_id', $order->id)->get();

        foreach ($oldOrderDetails as $oldOrderItem) {
            // Update stock quantity in the product -> reverse
            if ($oldOrderStatus == Order::STATUS_APPROVED) {
                updateStock($oldOrderItem->product_id, $oldOrderItem->quantity, 'add');
            }

            $oldOrderItem->delete();
        }
    }

    // public function asyncCalculateTotal(Repository $repository): Repository
    // {
    //     return $repository;
    // }

}
