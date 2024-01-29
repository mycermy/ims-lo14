<?php

namespace App\Orchid\Screens\Product;

use App\Models\AdjustedProduct;
use App\Models\Product;
use App\Models\StockAdjustment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class StockAdjustment_EditScreen extends Screen
{
    public ?StockAdjustment $stockAdjustment = null;
    public $adjustedProduct;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(StockAdjustment $stockAdjustment): iterable
    {
        // $adjustedProduct = QueryBuilder::for(AdjustedProduct::class)->where('stock_adjustment_id', $adjustment->id)->get();
        // $adjustedProduct = AdjustedProduct::where('stock_adjustment_id', $adjustment->id)->get();

        return [
            'stockAdjustment' => $stockAdjustment,
            // 'adjustedProduct' => $adjustedProduct,
            'adjustedProduct' => $stockAdjustment->adjustedProducts()->get(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->stockAdjustment->exists ? 'Edit ' . $this->stockAdjustment->name : 'Create Stock Adjustment';
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
                ->canSee(!$this->stockAdjustment->exists)
                ->method('store'),

            Button::make(__('Update'))
                ->icon('bs.check-circle')
                ->canSee($this->stockAdjustment->exists)
                ->method('update'),

            Link::make(__('Cancel'))
                ->icon('bs.x-circle')
                ->route('platform.products.stockadjustments'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        $number = StockAdjustment::max('id') + 1;
        $refid = make_reference_id('ADJ', $number);
        $harini = now()->toDateString(); //dd($harini);
        return [
            Layout::rows([
                Group::make([
                    Input::make('stockAdjustment.reference')
                        ->title('Reference')
                        ->required()
                        ->value($refid)
                        ->disabled()
                        ->horizontal(),
                    //
                    DateTimer::make('stockAdjustment.date')
                        ->title('Date')
                        ->format('d M Y')
                        // ->serverFormat()
                        ->required()
                        ->value($harini)
                        ->allowInput()
                        ->horizontal(),
                    //
                ])->fullWidth(),
                // Layout::rows([
                    TextArea::make('stockAdjustment.note')
                        ->title('Note (If Needed)')
                        ->rows(3)
                        ->horizontal(),
                    //
                    Matrix::make('adjustedProduct')
                        ->title('Adjusted Products')
                        ->removeableRows(false)
                        ->columns(['id', 'Product' => 'product_id', 'quantity', 'type'])
                        ->fields([
                            'id' => Input::make('id')->readonly()->type('hidden'),
                            'product_id' => Relation::make('product_id')->fromModel(Product::class,'name')->readonly()->searchColumns('name','code','part_number')->chunk(10)->required(),
                            'quantity' => Input::make('quantity')->type('number')->required(),
                            'type' => Input::make('type')->required(),
                        ]),
                // ]),
            ]),

            // Layout::table('adjustedProduct',[
            //     TD::make('product_id','Code')->render(fn($target) => $target->product->code ?? null),
            //     TD::make('product_id','Product')->render(fn($target) => $target->product->name ?? null),
            //     TD::make('quantity', 'Adjust Qty')->alignCenter(),
            //     TD::make('type')->alignCenter(),
            //     //
            // ])->title('Adjusted Products Details'),
        ];
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, StockAdjustment $stockAdjustment)
    {
        $stockAdjustment->fill($request->get('stockAdjustment'));
        // $product->fill(['name' => strtoupper($request->input('product.name'))]);
        $stockAdjustment->fill(['updated_by' => auth()->id()]);
        
        $stockAdjustment->save();
        
        $adjustedProducts = $request->get('adjustedProduct');
        foreach ($adjustedProducts as $adjustedProduct) {
            $stockAdjustment->adjustedProducts()->create($adjustedProduct);
            // amend stock qty in product
            $product = Product::findOrFail($adjustedProduct['product_id']);

            if ($adjustedProduct['type'] == 'add') {
                # code...add
                $product->update([
                    'quantity' => $product->quantity + $adjustedProduct['quantity']
                ]);
            } elseif ($adjustedProduct['type'] == 'sub') {
                # code...sub
                $product->update([
                    'quantity' => $product->quantity - $adjustedProduct['quantity']
                ]);
            }
            
        }

        Toast::info(__('Stock adjustment was saved.'));

        return redirect()->route('platform.products.stockadjustments');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, StockAdjustment $stockAdjustment)
    {
        $stockAdjustment->fill($request->get('stockAdjustment'));
        $stockAdjustment->fill(['updated_by' => auth()->id()]);
        $stockAdjustment->save();

        $adjustedProducts = $request->get('adjustedProduct');
        foreach ($adjustedProducts as $adjustedProduct) {
            if (isset($adjustedProduct['id'])) {
                # code...
                $existingAdjustedProduct = $stockAdjustment->adjustedProducts()->find($adjustedProduct['id']);
                if ($existingAdjustedProduct) {
                    // get current stock
                    $product = Product::findOrFail($adjustedProduct['product_id']);
                    // rollback stock qty in product table
                    if ($existingAdjustedProduct['type'] == 'add') {
                        # code...
                        $product->update([
                            'quantity' => $product->quantity - $existingAdjustedProduct['quantity']
                        ]);
                    } elseif ($existingAdjustedProduct['type'] == 'sub') {
                        # code...
                        $product->update([
                            'quantity' => $product->quantity + $existingAdjustedProduct['quantity']
                        ]);
                    }
                    
                    // kemudian baru amend dengan adjust qty yang baru.
                    if ($adjustedProduct['type'] == 'add') {
                        # code...
                        $product->update([
                            'quantity' => $product->quantity + $adjustedProduct['quantity']
                        ]);
                    } elseif ($adjustedProduct['type'] == 'sub') {
                        # code...
                        $product->update([
                            'quantity' => $product->quantity - $adjustedProduct['quantity']
                        ]);
                    }

                    // update adjustedProduct table
                    $existingAdjustedProduct->update($adjustedProduct);
                    // $existingAdjustedProduct->save();
                }
            } else {
                # code...
                // cannot simply add on new item bila existing. yes, still there. tapi kita disable
                //$stockAdjustment->adjustedProducts()->create($adjustedProduct);
            }
            
        }

        Toast::info(__('Stock adjustment was updated.'));
        return redirect()->route('platform.products.stockadjustments');
    }

    
}
