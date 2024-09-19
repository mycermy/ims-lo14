<?php

namespace App\Orchid\Screens\Sales\OrderPayment;

use App\Models\Sales\Order;
use App\Models\Sales\OrderPayment;
use App\Rules\AmountNotExceedDue;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class OrderPayment_EditScreen extends Screen
{
    // built-in order id as default. 
    public ?Order $order = null;
    public ?OrderPayment $payment  = null;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Order $order, OrderPayment $payment): iterable
    {
        return [
            'order' => $order,
            'payment' => $payment,
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
            ($this->payment->exists
                ? 'Edit Payment: ' . $this->payment->reference
                : 'New Order Payment');
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
                ->canSee(!$this->payment->exists)
                ->method('store'),

            // Button::make(__('Update'))
            //     ->icon('bs.check-circle')
            //     ->canSee($this->OrderPayment->exists)
            //     ->method('store'),

            Link::make(__('Cancel'))
                ->icon('bs.x-circle')
                ->route('platform.orders.payments', $this->order),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        $number = OrderPayment::max('id') + 1;
        $refid = make_reference_id('SP', $number);
        $harini = now()->toDateString(); //dd($harini);

        return [
            Layout::rows([
                Group::make([
                    Input::make('payment.reference')
                        ->type('text')
                        ->required()
                        ->title('Reference')
                        ->value($refid)
                        ->readonly(),
                    //
                    DateTimer::make('payment.date')
                        ->title('Date')
                        ->format('d M Y')
                        ->required()
                        ->value($harini)
                        ->allowInput(),
                    //
                ])->fullWidth(),
                //
                Group::make([
                    Input::make('order.due_amount')
                        ->title('Due Amount')
                        ->value($this->order->due_amount)
                        ->readonly(),
                    //
                    Input::make('payment.amount')
                        ->title('Amount')
                        ->required(),
                    //
                    Select::make('payment.payment_method')
                        ->title('Payment Method')
                        ->options([
                            OrderPayment::PAYMENT_CASH => OrderPayment::PAYMENT_CASH,
                            OrderPayment::PAYMENT_QRCODE => OrderPayment::PAYMENT_QRCODE,
                            OrderPayment::PAYMENT_BANK_TRANSFER => OrderPayment::PAYMENT_BANK_TRANSFER,
                            OrderPayment::PAYMENT_CREDIT_CARD => OrderPayment::PAYMENT_CREDIT_CARD,
                            OrderPayment::PAYMENT_CHEQUE => OrderPayment::PAYMENT_CHEQUE,
                            OrderPayment::PAYMENT_OTHER => OrderPayment::PAYMENT_OTHER,
                            OrderPayment::PAYMENT_REFUND => OrderPayment::PAYMENT_REFUND,
                        ])
                        ->empty('No select')
                        ->required(),
                    //
                ])->fullWidth(),
                //
                TextArea::make('payment.note')
                    ->title('Note (If Needed)')
                    ->rows(3)
                    ->max(1000),
                // 
                Input::make('payment.order_id')
                    ->value($this->order->id)
                    ->type('hidden'),
                //
            ]),
        ];
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, OrderPayment $payment)
    {
        // kalo edit
        // if ($this->order->exists) {
        //     $this->removeOldorderDetails($order);
        // }
        // 

        $request->validate([
            'payment.date' => 'required|date',
            'payment.reference' => 'required|string|max:255',
            // 'payment.amount' => ['required', 'numeric', new AmountNotExceedDue($request->input('order.due_amount'))],
            'payment.amount' => [
                'required',
                'numeric',
                new AmountNotExceedDue($request->input('order.due_amount'), 'The payment amount should not exceed the due amount.')
            ],
            'payment.note' => 'nullable|string|max:1000',
            'payment.order_id' => 'required',
            'payment.payment_method' => 'required|string|max:255',
        ]);

        $payment->fill($request->get('payment'));
        $payment->fill([
            'date' => Carbon::parse($request->input('payment.date'))->toDate(),
            'updated_by' => auth()->id(),
        ]);
        $payment->save();


        // update order
        $order = Order::findOrFail($request->input('payment.order_id'));

        $paidAmount = $order->paid_amount + $request->input('payment.amount');

        $dueAmount = $order->due_amount - $request->input('payment.amount');

        $paymentStatus = match (true) {
            $dueAmount == $order->total_amount => OrderPayment::STATUS_UNPAID,
            $dueAmount > 0 => OrderPayment::STATUS_PARTIALLY_PAID,
            $dueAmount < 0 => OrderPayment::STATUS_OVERPAID,
            default => OrderPayment::STATUS_PAID,
        };

        $order->update([
            'paid_amount' => $paidAmount,
            'due_amount' => $dueAmount,
            'payment_status' => $paymentStatus,
            'status' => $paymentStatus == OrderPayment::STATUS_PAID ? Order::STATUS_COMPLETED : $order->status,
        ]);

        Toast::info(__('Order Payment was saved.'));

        return redirect()->route('platform.orders.payments', $order);
    }
}
