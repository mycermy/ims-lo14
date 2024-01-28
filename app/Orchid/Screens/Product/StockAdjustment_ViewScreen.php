<?php

namespace App\Orchid\Screens\Product;

use App\Models\StockAdjustment;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class StockAdjustment_ViewScreen extends Screen
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

        return [
            'stockAdjustment' => $stockAdjustment,
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
        return $this->stockAdjustment->exists ? 'View ' . $this->stockAdjustment->name : 'Create Stock Adjustment';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Edit'))
                ->icon('pencil')
                ->route('platform.products.stockadjustments.edit', $this->stockAdjustment),

            Link::make(__('Back'))
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
                        ->disabled()
                        ->horizontal(),
                    //
                    Input::make('stockAdjustment.date')
                        ->title('Date')
                        ->disabled()
                        ->horizontal(),
                    //
                ])->fullWidth(),

                //
                TextArea::make('stockAdjustment.note')
                    ->title('Note (If Needed)')
                    ->rows(3)
                    ->disabled()
                    ->horizontal()
                
            ]),
            

            Layout::table('adjustedProduct',[
                TD::make('product_id','Code')->render(fn($target) => $target->product->code ?? null),
                TD::make('product_id','Product')->render(fn($target) => $target->product->name ?? null),
                TD::make('quantity', 'Adjust Qty')->alignCenter(),
                TD::make('type')->alignCenter(),
                //
            ])->title('Adjusted Products Details'),
        ];
    }

    
}
