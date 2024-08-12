<?php

namespace App\Orchid\Screens\Purchase;

use Orchid\Screen\Screen;

class PurchaseReturn_EditScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'PurchaseReturn_EditScreen';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [];
    }

    public function store(Request $request)
    {
        $purchase = Purchase::findOrFail($request->input('purchase_id'));
        $items = $request->input('items'); // Array of items with 'purchase_item_id', 'quantity', 'amount'

        $totalAmount = array_sum(array_column($items, 'amount'));

        $purchaseReturn = PurchaseReturn::create([
            'purchase_id' => $purchase->id,
            'total_amount' => $totalAmount,
            'reason' => $request->input('reason'),
        ]);

        foreach ($items as $item) {
            PurchaseReturnItem::create([
                'purchase_return_id' => $purchaseReturn->id,
                'purchase_item_id' => $item['purchase_item_id'],
                'quantity' => $item['quantity'],
                'amount' => $item['amount'],
            ]);
        }

        // Update purchase amounts
        $purchase->update([
            'paid_amount' => $purchase->paid_amount - $totalAmount,
            'due_amount' => $purchase->due_amount + $totalAmount,
        ]);

        return response()->json(['message' => 'Purchase return processed successfully.']);
    }
}
