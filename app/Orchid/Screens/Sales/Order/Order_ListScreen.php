<?php

namespace App\Orchid\Screens\Sales\Order;

use App\Models\Sales\Order;
use App\Models\Sales\OrderPayment;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class Order_ListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'model' => Order::filters()->orderByDesc('date')->orderByDesc('id')->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Orders Listing';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Add'))
                ->icon('bs.plus-circle')
                ->route('platform.orders.create'),
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
            Layout::table('model', [
                TD::make('status')->alignCenter()
                    ->render(
                        function ($target) {
                            $class = ($target->status == Order::STATUS_APPROVED
                                || ($target->status == Order::STATUS_COMPLETED && $target->payment_status == OrderPayment::STATUS_PAID))
                                ? 'text-bg-success text-white'
                                : 'text-bg-danger';
                            return Link::make($target->status)
                                ->class($class . ' badge text-uppercase')
                                ->route('platform.orders.view', $target);
                        }
                    ),
                TD::make('date'),
                TD::make('reference')
                    ->render(
                        fn($target) =>
                        Link::make($target->reference)
                            ->route('platform.orders.view', $target)
                    ),
                TD::make('customer_name', 'Customer'),
                TD::make('total_amount', 'Total Amount')->alignRight(),
                TD::make('paid_amount', 'Paid Amount')->alignRight(),
                TD::make('due_amount', 'Due Amount')->alignRight(),
                TD::make('payment_status', 'Payment Status')->alignCenter()
                    ->render(function ($target) {
                        if ($target->payment_status == OrderPayment::STATUS_PAID) {
                            $button = 'text-bg-success text-white';
                        } elseif ($target->payment_status == OrderPayment::PAYMENT_REFUND) {
                            $button = 'text-bg-warning';
                        } else {
                            $button = 'text-bg-danger';
                        }
                        //
                        return Link::make($target->payment_status)
                            ->route('platform.orders.payments', $target)
                            ->class($button . ' badge text-uppercase');
                    }),

                // TD::make('actions')->alignCenter()
                //     ->canSee(Auth::user()->hasAnyAccess(['platform.systems.editor', 'platform.items.editor']))
                //     ->width('10px')
                //     ->render(
                //         fn($target) =>
                //         $this->getTableActions($target)
                //             ->alignCenter()
                //             ->autoWidth()
                //             ->render()
                //     ),
            ]),
        ];
    }
}
