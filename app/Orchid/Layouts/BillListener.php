<?php

namespace App\Orchid\Layouts;

use App\Models\Contact\Contact;
use App\Models\Product\Product;
use App\Models\Purchase\Purchase;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class BillListener extends Listener
{
    /**
     * List of field names for which values will be listened.
     *
     * @var string[]
     */
    protected $targets = [
        'purchase.supplier_id',
        'purchase.note',
        'purchase.status',
    ];

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    protected function layouts(): iterable
    {
        $number = Purchase::max('id') + 1;
        $refid = make_reference_id('PR', $number);
        // $harini = now()->toDateString(); //dd($harini);
        $harini = now()->format('d M Y'); //dd($harini);

        return [
            Layout::rows([
                Group::make([
                    Input::make('purchase.reference')
                        ->title('Reference')
                        ->required()
                        ->value($refid)
                        ->disabled(),
                    //
                    DateTimer::make('purchase.date')
                        ->title('Date')
                        ->format('d M Y')
                        ->required()
                        ->value($harini)
                        ->allowInput(),
                    //
                    Relation::make('purchase.supplier_id')
                        ->title('Supplier')
                        ->fromModel(Contact::class, 'name')
                        ->applyScope('supplier')
                        ->searchColumns('name', 'phone', 'email')
                        ->chunk(10)
                        ->required(),
                ])->fullWidth(),
                //
                TextArea::make('purchase.note')
                    ->title('Note (If Needed)')
                    ->rows(3)
                    ->horizontal(),
                // 
                Matrix::make('purchaseItems')
                    ->title('Purchase Details')
                    ->removeableRows(false)
                    ->columns(['id', 'Product' => 'product_id', 'quantity', 'Unit Price' => 'unit_price', 'sub_total'])
                    ->fields([
                        'id' => Input::make('id')->readonly()->type('hidden'),
                        'product_id' => Relation::make('product_id')->fromModel(Product::class, 'name')->readonly()->searchColumns('name', 'code', 'part_number')->chunk(10)->required(),
                        'quantity' => Input::make('quantity')->type('number')->required(),
                        'unit_price' => Input::make('unit_price')->required(),
                        'sub_total' => Input::make('sub_total')->readonly(),
                    ]),
                //
                Input::make('purchaseTotal')
                    ->title('Total')
                    ->readonly()
                    ->horizontal(),
                //
                Select::make('purchase.status')
                    ->title('Purchase Status')
                    ->options([
                        Purchase::STATUS_PENDING => Purchase::STATUS_PENDING,
                        Purchase::STATUS_APPROVED => Purchase::STATUS_APPROVED,
                    ])
                    // ->empty('No select')
                    ->horizontal(),
            ]),
        ];
    }

    /**
     * Update state
     *
     * @param \Orchid\Screen\Repository $repository
     * @param \Illuminate\Http\Request  $request
     *
     * @return \Orchid\Screen\Repository
     */
    public function handle(Repository $repository, Request $request): Repository
    {
        $items = $request->get('purchaseItems', []);
        $modifiedItems = [];
        $totalAmount = 0;

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);

            $quantity = floatval($item['quantity'] ?? 0);
            $price = floatval($item['unit_price'] ?? 0);
            $subTotal = $quantity * $price;
            
            $item['sub_total'] = number_format($subTotal, 2);
            $modifiedItems[] = $item;

            $totalAmount += $subTotal;
        }

        return $repository
            ->set('purchaseItems', array_values($modifiedItems))
            ->set('purchaseTotal', number_format($totalAmount, 2))
            ->set('purchase.supplier_id', $request->input('purchase.supplier_id'))
            ->set('purchase.status', $request->input('purchase.status'))
            ->set('purchase.note', $request->input('purchase.note'))
        ;
    }
}
