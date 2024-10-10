<?php

namespace App\Orchid\Screens\Sales\Order;

use Carbon\Carbon;
use App\Models\Product\Product;
use App\Models\Contact\Customer;
use Orchid\Screen\Screen;
use App\Models\Sales\Order;
use Illuminate\Http\Request;
use App\Models\Sales\OrderItem;
use App\Orchid\Layouts\OrderListener;
use App\Orchid\Support\Facades\Layout_mod;
use Gloudemans\Shoppingcart\Facades\Cart;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class Order_EditScreen extends Screen
{
    public ?Order $order = null;
    public $orderItems = [];
    public $totalAmount = 0;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Order $order): iterable
    {
        $this->order = $order;
        $this->orderItems = $order->orderItems()->get()->toArray();
        $this->calculateTotal();

        Cart::instance('sale')->destroy();
        $this->order->exists ?? Cart::instance('sale')->destroy();

        return [
            'order' => $this->order,
            'orderItems' => $this->orderItems,
            'totalAmount' => $this->totalAmount,
            'cartInstance' => 'sale',
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
            Layout_mod::livewire('SearchProduct'),
            Layout_mod::livewire('ProductCart'),
            // OrderListener::class,
            // Layout::view('Sales.order-create'),
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

    public function addOrderItem()
    {
        $this->orderItems[] = [
            'product_id' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'sub_total' => 0,
        ];
    }

    public function removeOrderItem($index)
    {
        unset($this->orderItems[$index]);
        $this->orderItems = array_values($this->orderItems);
        $this->calculateTotal();
    }

    public function updateOrderItem($index, $field, $value)
    {
        $this->orderItems[$index][$field] = $value;

        if ($field === 'product_id') {
            $product = Product::find($value);
            if ($product) {
                $this->orderItems[$index]['unit_price'] = $product->sell_price;
            }
        }

        $this->calculateSubTotal($index);
        $this->calculateTotal();
    }

    private function calculateSubTotal($index)
    {
        $item = $this->orderItems[$index];
        $this->orderItems[$index]['sub_total'] = $item['quantity'] * $item['unit_price'];
    }

    private function calculateTotal()
    {
        $this->totalAmount = array_sum(array_column($this->orderItems, 'sub_total'));
    }

    public function generatePDF()
    {
        // Implementation for generating PDF
        // ...

        Toast::info(__('PDF generated successfully.'));
    }

    public function generateExcel()
    {
        // Implementation for generating Excel
        // ...

        Toast::info(__('Excel file generated successfully.'));
    }

}
