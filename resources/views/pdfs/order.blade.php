@php
    use Carbon\Carbon;
@endphp

<!DOCTYPE html>
<html>

<head>
    <title>Order #{{ $order->reference }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .header {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Sales Order</h1>
        <p>Reference: {{ $order->reference }}</p>
        <p>Date: {{ Carbon::parse($order->date)->format('d M Y') }}</p>
        <p>Customer: {{ $order->customer_name }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orderItems as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Total Amount</td>
                <td>{{ number_format($order->total_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>

</html>
