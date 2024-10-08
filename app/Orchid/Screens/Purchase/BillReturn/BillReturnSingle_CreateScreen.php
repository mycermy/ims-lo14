<?php

namespace App\Orchid\Screens\Purchase\BillReturn;

use App\Models\Product\Product;
use App\Models\Purchase\Purchase;
use App\Models\Purchase\PurchaseDetail;
use App\Models\Purchase\PurchasePayment;
use App\Models\Purchase\PurchaseReturn;
use App\Models\Purchase\PurchaseReturnItem;
use App\Rules\ValueNotExceed;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class BillReturnSingle_CreateScreen extends Screen
{
    public ?Purchase $purchase = null;
    public ?PurchaseDetail $purchaseDetail = null;
    public ?PurchaseReturn $return = null;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Purchase $purchase, PurchaseDetail $purchaseDetail, PurchaseReturn $return): iterable
    {
        return [
            'purchase' => $purchase,
            'purchaseDetail' => $purchaseDetail,
            'purchaseItem' => $purchase->purchaseDetails()->where('id', $purchaseDetail->id)->get(),
            'return' => $return,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Bill: ' . $this->purchase->reference . ' >> ' .
            ($this->return->exists
                ? 'Edit Return: ' . $this->return->reference
                : 'New Purchase Return');
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
                // ->rawClick()
                ->canSee(!$this->return->exists)
                ->method('store'),

            Link::make(__('Cancel'))
                ->icon('bs.x-circle')
                ->route('platform.purchases.view', $this->purchase),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        $number = PurchaseReturn::max('id') + 1;
        $refid = make_reference_id('PRRN', $number);
        $harini = now()->toDateString(); //dd($harini);

        $quantityReturnBal = $this->purchaseDetail->quantity - $this->purchaseDetail->quantity_return;

        return [
            Layout::table('purchaseItem', [
                TD::make('id'),
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
                TD::make('quantity', 'Qty')->alignCenter(),
                TD::make('unit_price', 'Unit Price')->alignRight(),
                TD::make('sub_total', 'Total')->alignRight(),
                TD::make('quantity_return', 'QtyReturn')->alignCenter(),
            ])->title('Purchase Item'),

            Layout::rows([
                Group::make([
                    Input::make('returnItem.quantity')->title('Quantity')->type('number')
                        ->min(0)->max($quantityReturnBal)
                        ->required(),
                    
                    TextArea::make('return.reason')
                        ->title('Reason For Return')
                        ->required()
                        ->rows(3)
                        ->max(1000),
                    //
                ]),
                // 
                Input::make('returnItem.purchase_detail_id')
                    ->value($this->purchaseDetail->id)
                    ->type('hidden'),
                // 
                Input::make('returnItem.product_id')
                    ->value($this->purchaseDetail->product_id)
                    ->type('hidden'),
                //
                Input::make('returnItem.quantity_return_bal')
                    ->value($quantityReturnBal)
                    ->type('hidden'),
                //
                Input::make('returnItem.unit_price')
                    ->value($this->purchaseDetail->unit_price)
                    ->type('hidden'),
                // 
                Input::make('return.reference')
                    ->value($refid)
                    ->type('hidden'),
                //
                Input::make('return.purchase_id')
                    ->value($this->purchase->id)
                    ->type('hidden'),
                //
            ]),
        ];
    }

    public function store(Request $request, PurchaseReturn $purchaseReturn)
    {
        $request->validate([
            'return.reference' => 'required|string|max:255',
            'returnItem.quantity' => [
                'required',
                'numeric',
                new ValueNotExceed($request->input('returnItem.quantity_return_bal'), 
                'The return quantity should not exceed the purchase quantity.')
            ],
            'return.purchase_id' => 'required',
        ]);
        
        // dd($request->get('return'), $request->get('returnItem'));

        $returnItem = $request->get('returnItem');

        $subTotal = $returnItem['quantity'] * $returnItem['unit_price'];
        // $subTotal = $request->input('returnItem.quantity') * $request->input('purchaseItem.unit_price');
        $newReturnItem = new PurchaseReturnItem($returnItem);
        $newReturnItem->sub_total = $subTotal;


        $purchaseReturn->fill($request->get('return'));
        $purchaseReturn->fill([
            'total_amount' => $subTotal,
            'updated_by' => auth()->id(),
        ]);
        $purchaseReturn->save();
        
        // Associate the new PurchaseReturnItem with the $purchaseReturn model
        $purchaseReturn->returnItems()->save($newReturnItem);

        // update PurchaseDetail Quantity Return
        $purchaseItem = PurchaseDetail::findOrFail($returnItem['purchase_detail_id']);
        $purchaseItem->update(['quantity_return' => $purchaseItem->quantity_return + $returnItem['quantity']]);

        // Update stock quantity in the product
        updateStock($returnItem['product_id'], $returnItem['quantity'], 'purchaseReturn');


        // Update purchase amounts
        // 
        // Paid = Total - Due
        // Paid - PR = Total - Due - PR , PR == purchase return
        // Paid = Total - Due - PR + PR
        // Paid = (Total - PR) - (Due - PR)
        // Paid must be fixed
        // can conclude that Total => Total - PR, Due => Due - PR -> also mean Due after PR
        // (Due - PR) = (Total - PR) - Paid
        // 
        $purchase = Purchase::findOrFail($request->input('return.purchase_id'));

        $totalAmountReturn = $purchase->total_amount_return + $subTotal;
        $newTotalAmount = $purchase->total_amount - $totalAmountReturn;
        $dueAmount = $newTotalAmount - $purchase->paid_amount;

        // Adjust paid amount if necessary
        $newPaidAmount = $purchase->paid_amount;
        $refundAmount = 0;
        if ($dueAmount < 0) {
            $newPaidAmount += $dueAmount; // Refund the excess amount
            $refundAmount = $dueAmount; // Refund the excess amount
            $dueAmount = 0;
        }

        $paymentStatus = match (true) {
            $dueAmount == 0 && $newTotalAmount == 0 => PurchasePayment::PAYMENT_REFUND,
            $dueAmount == 0 && $newPaidAmount == $newTotalAmount => PurchasePayment::STATUS_PAID,
            $dueAmount == $newTotalAmount => PurchasePayment::STATUS_UNPAID,
            $dueAmount > 0 => PurchasePayment::STATUS_PARTIALLY_PAID,
            default => PurchasePayment::STATUS_OVERPAID,
        };

        $purchase->update([
            // 'total_amount' => $purchase->total_amount, // tak perlu update
            'total_amount_return' => $totalAmountReturn,
            'paid_amount' => $newPaidAmount,
            'due_amount' => $dueAmount,
            'payment_status' => $paymentStatus,
            'status' => $paymentStatus == PurchasePayment::STATUS_PAID ? Purchase::STATUS_COMPLETED : $purchase->status,
        ]);

        // Record the payment adjustment in PurchasePayment
        if ($refundAmount < 0) {
            $number = PurchasePayment::max('id') + 1;
            $refid = make_reference_id('PVRT', $number);
            $harini = now()->toDateString();
            PurchasePayment::create([
                'purchase_id' => $purchase->id,
                'reference' => $refid,
                'date' => $harini,
                'amount' => $refundAmount, // Store the refund amount as a negative value
                'payment_method' => PurchasePayment::PAYMENT_REFUND,
                'note' => 'Refund from Purchase Return #' . $purchaseReturn->reference,
                'created_by' => auth()->id(),
            ]);
        }

        Toast::info(__('Purchase return processed successfully.'));

        return redirect()->route('platform.purchases.returns', $this->purchase);
    }
}
