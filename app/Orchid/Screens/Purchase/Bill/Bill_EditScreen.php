<?php

namespace App\Orchid\Screens\Purchase\Bill;

use App\Models\Purchase\Purchase;
use App\Models\Purchase\PurchaseDetail;
use App\Models\Contact\Supplier;
use App\Orchid\Layouts\BillListener;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class Bill_EditScreen extends Screen
{
    public ?Purchase $purchase = null;
    public $item;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Purchase $purchase): iterable
    {
        return [
            'purchase' => $purchase,
            'purchaseItems' => $purchase->purchaseDetails()->get(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->purchase->exists ? 'Edit ' . $this->purchase->reference : 'New Purchase Bill';
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
                ->canSee(!$this->purchase->exists)
                ->method('store'),

            Button::make(__('Update'))
                ->icon('bs.check-circle')
                ->canSee($this->purchase->exists)
                ->method('store'),

            Link::make(__('Cancel'))
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
            BillListener::class,
        ];
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Purchase $purchase)
    {
        // kalo edit
        if ($this->purchase->exists) {
            $this->removeOldPurchaseDetails($purchase);
        }
        // 
        $supplier = Supplier::findOrFail($request->input('purchase.supplier_id'));

        $purchase->fill($request->get('purchase'));
        $purchase->fill([
            'date' => Carbon::parse($request->input('purchase.date'))->toDate(),
            'supplier_name' => $supplier->name,
            'updated_by' => auth()->id(),
        ]);
        $purchase->save();

        $totalAmount = 0;

        $items = $request->get('purchaseItems');
        foreach ($items as $item) {
            $subTotal = $item['quantity'] * $item['unit_price'];

            // Create a new PurchaseDetail instance
            $newPurchaseDetail = new PurchaseDetail($item);
            $newPurchaseDetail->sub_total = $subTotal; // Set the sub_total attribute

            // Associate the new PurchaseDetail with the $purchase model
            $purchase->purchaseDetails()->save($newPurchaseDetail);

            // Update stock quantity in the product
            if ($request->input('purchase.status') == Purchase::STATUS_APPROVED) {
                updateStock($item['product_id'], $item['quantity'], 'purchase');
            }
            //
            $totalAmount += $subTotal;
        }

        $purchase->fill(['total_amount' => $totalAmount, 'due_amount' => $totalAmount])->save();

        Toast::info(__('Purchase bill was saved.'));

        return redirect()->route('platform.purchases.view', $purchase);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeOldPurchaseDetails(Purchase $purchase)
    {
        $oldPurchaseStatus = $purchase->status ?? null;

        $oldPurchaseDetails = PurchaseDetail::where('purchase_id', $purchase->id)->get();

        foreach ($oldPurchaseDetails as $oldPurchaseDetail) {
            // Update stock quantity in the product -> reverse
            if ($oldPurchaseStatus == Purchase::STATUS_APPROVED) {
                updateStock($oldPurchaseDetail->product_id, $oldPurchaseDetail->quantity, 'purchaseRemove');
            }

            $oldPurchaseDetail->delete();
        }
    }

    // public function approve(Purchase $purchase)
    // {
    //     $items = PurchaseDetail::where('purchase_id', $purchase->id)->get();

    //     foreach ($items as $item) {
    //         // Product::where('id', $product->product_id)
    //         //         ->update(['quantity' => DB::raw('quantity+'.$product->quantity)]);
    //         updateStock($item->product_id, $item->quantity, 'purchase');
    //     }

    //     Purchase::findOrFail($purchase->id)
    //         ->update([
    //             'status' => Purchase::STATUS_APPROVED,
    //             'updated_by' => auth()->id(),
    //         ]);

    //     return redirect()
    //         ->back()
    //         ->with('success', 'Purchase has been approved!');
    // }

    // // hanya terpakai pada Purchase::STATUS_APPROVED dan Purchase::STATUS_UNPAID
    // public function approvedRevoke(Purchase $purchase)
    // {
    //     $items = PurchaseDetail::where('purchase_id', $purchase->id)->get();

    //     foreach ($items as $item) {
    //         // Product::where('id', $product->product_id)
    //         //         ->update(['quantity' => DB::raw('quantity+'.$product->quantity)]);
    //         updateStock($item->product_id, $item->quantity, 'purchaseRevoke');
    //     }

    //     Purchase::findOrFail($purchase->id)
    //         ->update([
    //             'status' => Purchase::STATUS_PENDING,
    //             'updated_by' => auth()->id(),
    //         ]);

    //     return redirect()
    //         ->back()
    //         ->with('success', 'Purchase approval has been revoked!');
    // }
}
