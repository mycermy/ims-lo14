<?php

namespace App\Orchid\Screens\Purchase;

use App\Models\Contact;
use App\Models\Product;
use App\Models\Purchase;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class Bill_ViewScreen extends Screen
{
    public ?Purchase $purchase = null;
    public $purchaseDetail;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Purchase $purchase): iterable
    {
        return [
            'purchase' => $purchase,
            'purchaseDetail' => $purchase->purchaseDetails()->get(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->purchase->exists ? 'View ' . $this->purchase->reference : 'New Purchase Bill';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Back'))
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
        $number = Purchase::max('id') + 1;
        $refid = make_reference_id('PR', $number);
        $harini = now()->toDateString(); 

        return [
            Layout::rows([
                Group::make([
                    Input::make('purchase.reference')
                        ->title('Reference')
                        ->required()
                        ->value($refid)
                        ->disabled()
                        ->horizontal(),
                    //
                    DateTimer::make('purchase.date')
                        ->title('Date')
                        ->format('d M Y')
                        ->required()
                        ->value($harini)
                        ->allowInput()
                        ->horizontal(),
                    // 
                    Relation::make('purchase.supplier_id')
                    ->title('Supplier')
                    ->fromModel(Contact::class, 'name')
                    ->applyScope('supplier')
                    ->searchColumns('name', 'phone', 'email')
                    ->chunk(10)
                    ->required()
                    ->horizontal(),
                    // 
                ])->fullWidth(),
                // 
            ]),

            Layout::table('purchaseDetail', [
                TD::make('id', '#')->render(fn ($target, object $loop) => $loop->iteration + (request('page') > 0 ? (request('page') - 1) * $target->getPerPage() : 0)),
                TD::make('product_id','Code')->render(fn($target) => $target->product->code ?? null),
                TD::make('product_id','Product')->render(fn($target) => $target->product->name ?? null),
                TD::make('quantity', 'Qty')->alignCenter(),
                TD::make('unit_price', 'Unit Price')->alignRight(),
                TD::make('sub_total', 'Total')->alignRight(),
            ])->title('Purchase Details'),
        ];
    }
}
