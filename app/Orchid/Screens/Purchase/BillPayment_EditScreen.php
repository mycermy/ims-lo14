<?php

namespace App\Orchid\Screens\Purchase;

use App\Models\Purchase;
use App\Models\PurchasePayment;
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

class BillPayment_EditScreen extends Screen
{
    // built-in purchase id as default. 
    public ?Purchase $purchase = null;
    public ?PurchasePayment $payment  = null;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Purchase $purchase, PurchasePayment $payment): iterable
    {
        return [
            'purchase' => $purchase,
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
        return 'Bill: ' . $this->purchase->reference . ' >> ' .
            ($this->payment->exists
            ? 'Edit Payment: ' . $this->payment->reference
            : 'New Bill Payment');
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
            //     ->canSee($this->purchasePayment->exists)
            //     ->method('store'),

            Link::make(__('Cancel'))
                ->icon('bs.x-circle')
                ->route('platform.purchases.payments', $this->purchase),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        $number = PurchasePayment::max('id') + 1;
        $refid = make_reference_id('PV', $number);
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
                    Input::make('purchase.due_amount')
                        ->title('Due Amount')
                        ->value($this->purchase->due_amount)
                        ->readonly(),
                    //
                    Input::make('payment.amount')
                        ->title('Amount')
                        ->required(),
                    //
                    Select::make('payment.payment_method')
                        ->title('Payment Method')
                        ->options([
                            PurchasePayment::PAYMENT_CASH => PurchasePayment::PAYMENT_CASH,
                            PurchasePayment::PAYMENT_QRCODE => PurchasePayment::PAYMENT_QRCODE,
                            PurchasePayment::PAYMENT_BANK_TRANSFER => PurchasePayment::PAYMENT_BANK_TRANSFER,
                            PurchasePayment::PAYMENT_CREDIT_CARD => PurchasePayment::PAYMENT_CREDIT_CARD,
                            PurchasePayment::PAYMENT_CHEQUE => PurchasePayment::PAYMENT_CHEQUE,
                            PurchasePayment::PAYMENT_OTHER => PurchasePayment::PAYMENT_OTHER,
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
                Input::make('payment.purchase_id')
                    ->value($this->purchase->id)
                    ->type('hidden'),
                //
            ]),
        ];
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, PurchasePayment $payment)
    {
        // kalo edit
        // if ($this->purchase->exists) {
        //     $this->removeOldPurchaseDetails($purchase);
        // }
        // 

        $request->validate([
            'payment.date' => 'required|date',
            'payment.reference' => 'required|string|max:255',
            'payment.amount' => 'required|numeric',
            'payment.note' => 'nullable|string|max:1000',
            'payment.purchase_id' => 'required',
            'payment.payment_method' => 'required|string|max:255',
        ]);

        $payment->fill($request->get('payment'));
        $payment->fill(['date' => Carbon::parse($request->input('payment.date'))->toDate(),]);
        $payment->save();


        // update purchase
        $purchase = Purchase::findOrFail($request->input('payment.purchase_id'));

        $paid_amount = $purchase->paid_amount + $request->input('payment.amount');

        $due_amount = $purchase->due_amount - $request->input('payment.amount');

        $payment_status = match (true) {
            $due_amount == $purchase->total_amount => PurchasePayment::STATUS_UNPAID,
            $due_amount > 0 => PurchasePayment::STATUS_PARTIALLY_PAID,
            $due_amount < 0 => PurchasePayment::STATUS_OVERPAID,
            default => PurchasePayment::STATUS_PAID,
        };

        $purchase->update([
            'paid_amount' => $paid_amount,
            'due_amount' => $due_amount,
            'payment_status' => $payment_status,
            'status' => $payment_status == PurchasePayment::STATUS_PAID ? Purchase::STATUS_COMPLETED : $purchase->status,
        ]);

        Toast::info(__('Purchase Payment was saved.'));

        return redirect()->route('platform.purchases.payments', $purchase);
    }
}
