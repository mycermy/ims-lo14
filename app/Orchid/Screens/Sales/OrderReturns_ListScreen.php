<?php

namespace App\Orchid\Screens\Sales;

use App\Models\Sales\OrderReturn;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Layouts\Persona;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class OrderReturns_ListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'model' => OrderReturn::filters()->orderByDesc('created_at')->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Order Returns Listing ';
    }

    /**
     * Display header description.
     */
    // public function description(): ?string
    // {
    //     return 'Payments done for this bill.';
    // }

    /**
     * The permissions required to access this screen.
     */
    // public function permission(): ?iterable
    // {
    //     return [
    //         'platform.systems.roles',
    //     ];
    // }

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
        return [
            Layout::table('model', [
                // TD::make('id', '#')->render(fn($target, object $loop) => $loop->iteration + (getPage() - 1) * $target->getPerPage()),
                TD::make('created_at','Date')->width(150)->asComponent(DateTimeSplit::class),
                TD::make('order')->width(150)
                    ->render(
                        fn($target) =>
                        Link::make($target->order->reference)
                            ->route('platform.orders.view', $target->order)
                    ),
                TD::make('reference', 'Return Reference')//->width(150)
                    ->render(fn ($target) => new Persona($target->presenter())),
                TD::make('total_amount', 'Total Amount')->alignRight()->width(50),
                // TD::make('reason'),
                TD::make('updated_by', 'Updated By')->alignRight()->width(150)
                    ->render(fn($target) => $target->updatedBy->name ?? null),
            ]),
        ];
    }
}