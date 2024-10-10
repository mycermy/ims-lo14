<div>
    <form wire:submit.prevent="store">
        <!-- Order details fields -->
        <div>
            <label>Customer</label>
            <select wire:model="order.customer_id">
                <!-- Customer options -->
            </select>
        </div>

        <!-- Order items -->
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Subtotal</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orderItems as $index => $item)
                    <tr>
                        <td>
                            <select wire:model="orderItems.{{ $index }}.product_id" 
                                    wire:change="updateOrderItem({{ $index }}, 'product_id', $event.target.value)">
                                <!-- Product options -->
                            </select>
                        </td>
                        <td>
                            <input type="number" wire:model="orderItems.{{ $index }}.quantity" 
                                   wire:change="updateOrderItem({{ $index }}, 'quantity', $event.target.value)">
                        </td>
                        <td>{{ $item['unit_price'] }}</td>
                        <td>{{ $item['sub_total'] }}</td>
                        <td>
                            <button wire:click.prevent="removeOrderItem({{ $index }})">Remove</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button wire:click.prevent="addOrderItem">Add Item</button>

        <div>
            <strong>Total: {{ $totalAmount }}</strong>
        </div>

        <button type="submit">Save Order</button>
    </form>
</div>