<?php

namespace App\Orchid\Screens\Purchase;

use App\Models\Purchase\PurchasePayment;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Persona;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class PurchasePayments_ListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'model' => PurchasePayment::filters()->orderByDesc('created_at')->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Purchase Payments Listing ';
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
                TD::make('date')->width(130),
                TD::make('bill')->width(150)
                    ->render(
                        fn($target) =>
                        Link::make($target->purchase->reference)
                            ->route('platform.purchases.view', $target->purchase)
                    ),
                TD::make('reference')//->width(150)
                    ->render(fn($target) => new Persona($target->presenter())),
                    // ->render(
                    //     fn($target) =>
                    //     Link::make($target->reference)
                    //         ->route('platform.purchases.payments', $target->purchase)
                    // ),
                TD::make('payment_method', 'Payment Method')->alignCenter()->width(150),
                TD::make('amount')->alignRight()->width(50),
                // TD::make('note'),
                TD::make('updated_by', 'Updated By')->alignRight()->width(150)
                    ->render(fn($target) => $target->updatedBy->name ?? null),
            ]),
        ];
    }
}
