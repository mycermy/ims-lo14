<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Sales\Order;
use App\Models\Product\Product;

class OrderEditComponent extends Component
{
    public $order;
    public $orderItems = [];

    public function mount(Order $order = null)
    {
        $this->order = $order ?? new Order();
        $this->orderItems = $order ? $order->orderItems->toArray() : [];
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
    }

    private function calculateSubTotal($index)
    {
        $item = $this->orderItems[$index];
        $this->orderItems[$index]['sub_total'] = $item['quantity'] * $item['unit_price'];
    }

    public function getTotalProperty()
    {
        return array_sum(array_column($this->orderItems, 'sub_total'));
    }

    public function render()
    {
        return view('livewire.order-edit-component');
    }
}