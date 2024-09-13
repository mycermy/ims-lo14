<?php

namespace App\Orchid\Screens\Sales\OrderReturn;

use App\Models\Sales\Order;
use App\Models\Sales\OrderItem;
use App\Models\Sales\OrderPayment;
use App\Models\Sales\OrderReturn;
use App\Models\Sales\OrderReturnItem;
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

class OrderReturnSingle_CreateScreen extends Screen
{
    public ?Order $order = null;
    public ?OrderItem $orderDetail = null;
    public ?OrderReturn $return = null;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Order $order, OrderItem $orderDetail, OrderReturn $return): iterable
    {
        return [
            'order' => $order,
            'orderDetail' => $orderDetail,
            'orderItem' => $order->orderItems()->where('id', $orderDetail->id)->get(),
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
        return 'Order: ' . $this->order->reference . ' >> ' .
            ($this->return->exists
                ? 'Edit Return: ' . $this->return->reference
                : 'New Order Return');
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
                ->route('platform.orders.view', $this->order),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        $number = OrderReturn::max('id') + 1;
        $refid = make_reference_id('ODRN', $number);
        $harini = now()->toDateString(); //dd($harini);

        $quantityReturnBal = $this->orderDetail->quantity - $this->orderDetail->quantity_return;

        return [
            Layout::table('orderItem', [
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
            ])->title('Order Item'),

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
                Input::make('returnItem.order_item_id')
                    ->value($this->orderDetail->id)
                    ->type('hidden'),
                // 
                Input::make('returnItem.product_id')
                    ->value($this->orderDetail->product_id)
                    ->type('hidden'),
                //
                Input::make('returnItem.quantity_return_bal')
                    ->value($quantityReturnBal)
                    ->type('hidden'),
                //
                Input::make('returnItem.unit_price')
                    ->value($this->orderDetail->unit_price)
                    ->type('hidden'),
                // 
                Input::make('return.reference')
                    ->value($refid)
                    ->type('hidden'),
                //
                Input::make('return.order_id')
                    ->value($this->order->id)
                    ->type('hidden'),
                //
            ]),
        ];
    }

    public function store(Request $request, OrderReturn $orderReturn)
    {
        $request->validate([
            'return.reference' => 'required|string|max:255',
            'returnItem.quantity' => [
                'required',
                'numeric',
                new ValueNotExceed($request->input('returnItem.quantity_return_bal'), 
                'The return quantity should not exceed the order quantity.')
            ],
            'return.order_id' => 'required',
        ]);
        
        // dd($request->get('return'), $request->get('returnItem'));

        $returnItem = $request->get('returnItem');

        $subTotal = $returnItem['quantity'] * $returnItem['unit_price'];
        // $subTotal = $request->input('returnItem.quantity') * $request->input('orderItem.unit_price');
        $newReturnItem = new OrderReturnItem($returnItem);
        $newReturnItem->sub_total = $subTotal;


        $orderReturn->fill($request->get('return'));
        $orderReturn->fill([
            'total_amount' => $subTotal,
            'updated_by' => auth()->id(),
        ]);
        $orderReturn->save();
        
        // Associate the new OrderReturnItem with the $orderReturn model
        $orderReturn->returnItems()->save($newReturnItem);

        // update OrderItem Quantity Return
        $orderItem = OrderItem::findOrFail($returnItem['order_item_id']);
        $orderItem->update(['quantity_return' => $orderItem->quantity_return + $returnItem['quantity']]);

        // Update stock quantity in the product
        updateStock($returnItem['product_id'], $returnItem['quantity'], 'add');


        // Update order amounts
        // 
        // Paid = Total - Due
        // Paid - PR = Total - Due - PR , PR == order return
        // Paid = Total - Due - PR + PR
        // Paid = (Total - PR) - (Due - PR)
        // Paid must be fixed
        // can conclude that Total => Total - PR, Due => Due - PR -> also mean Due after PR
        // (Due - PR) = (Total - PR) - Paid
        // 
        $order = Order::findOrFail($request->input('return.order_id'));

        $totalAmountReturn = $order->total_amount_return + $subTotal;
        $newTotalAmount = $order->total_amount - $totalAmountReturn;
        $dueAmount = $newTotalAmount - $order->paid_amount;

        // Adjust paid amount if necessary
        $newPaidAmount = $order->paid_amount;
        $refundAmount = 0;
        if ($dueAmount < 0) {
            $newPaidAmount += $dueAmount; // Refund the excess amount
            $refundAmount = $dueAmount; // Refund the excess amount
            $dueAmount = 0;
        }

        $paymentStatus = match (true) {
            $dueAmount == 0 && $newTotalAmount == 0 => OrderPayment::PAYMENT_REFUND,
            $dueAmount == 0 && $newPaidAmount == $newTotalAmount => OrderPayment::STATUS_PAID,
            $dueAmount == $newTotalAmount => OrderPayment::STATUS_UNPAID,
            $dueAmount > 0 => OrderPayment::STATUS_PARTIALLY_PAID,
            default => OrderPayment::STATUS_OVERPAID,
        };

        $order->update([
            // 'total_amount' => $order->total_amount, // tak perlu update
            'total_amount_return' => $totalAmountReturn,
            'paid_amount' => $newPaidAmount,
            'due_amount' => $dueAmount,
            'payment_status' => $paymentStatus,
            'status' => $paymentStatus == OrderPayment::STATUS_PAID ? Order::STATUS_COMPLETED : $order->status,
        ]);

        // Record the payment adjustment in OrderPayment
        if ($refundAmount < 0) {
            $number = OrderPayment::max('id') + 1;
            $refid = make_reference_id('OPRT', $number);
            $harini = now()->toDateString();
            OrderPayment::create([
                'order_id' => $order->id,
                'reference' => $refid,
                'date' => $harini,
                'amount' => $refundAmount, // Store the refund amount as a negative value
                'payment_method' => OrderPayment::PAYMENT_REFUND,
                'note' => 'Refund from Order Return #' . $orderReturn->reference,
                'created_by' => auth()->id(),
            ]);
        }

        Toast::info(__('Order return processed successfully.'));

        return redirect()->route('platform.orders.returns', $this->order);
    }
}
