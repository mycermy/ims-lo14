<?php

namespace App\Orchid\Screens\Product;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class Product_EditScreen extends Screen
{
    public ?Product $product = null;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Product $product): iterable
    {
        return [
            'product' => $product,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->product->exists ? 'Edit ' . $this->product->name : 'Create Product';
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
                ->canSee(!$this->product->exists)
                ->method('store'),

            Button::make(__('Update'))
                ->icon('bs.check-circle')
                ->canSee($this->product->exists)
                ->method('update'),

            Link::make(__('Cancel'))
                ->icon('bs.x-circle')
                ->route('platform.products'),
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
            Layout::columns([
                Layout::rows([
                    Relation::make('product.category_id')
                        ->fromModel(Category::class, 'name')
                        ->title('Product Category')
                        ->horizontal(),
                    
                    TextArea::make('product.name')
                        ->title('Product Name')
                        ->rows('3')
                        ->required()
                        ->horizontal(),
                    
                    Input::make('product.code')
                        ->title('Product Code')
                        ->required()
                        ->horizontal(),
                    
                    Input::make('product.sell_price')
                        ->title('Selling Price')
                        ->required()
                        ->horizontal(),                    
                    
                ])->title(''),
                Layout::rows([
                    Input::make('product.part_number')
                        ->title('Part Number')
                        ->horizontal(),
                    
                    TextArea::make('product.compatible')
                        ->title('Product Compatible')
                        ->rows('6')
                        ->horizontal(),
                    
                    Switcher::make('product.is_nonstock')
                        ->sendTrueOrFalse()
                        ->title(__('Non-Stock Item'))
                        ->placeholder('Mark this as untrack sell item.')
                        ->horizontal(),
                    
                    // Button::make('Update')
                    //     ->type(Color::BASIC)
                    //     ->icon('bs.check-circle')
                    //     ->canSee($this->product->exists)
                    //     ->method('update'),
                ])->title(''),
            ]),
        ];
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'product.code' => [
                'required',
                Rule::unique(Product::class,'code')
            ]
        ]);
        
        $product->fill($request->get('product'));
        $product->fill(['name' => strtoupper($request->input('product.name'))]);
        $product->fill(['created_by' => auth()->id()]);

        $product->save();

        Toast::info(__('Product was saved.'));

        return redirect()->route('platform.products');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Product $product)
    {
        $product->fill($request->get('product'));
        $product->fill(['name' => strtoupper($request->input('product.name'))]);
        $product->fill(['updated_by' => auth()->id()]);

        $product->save();

        Toast::info(__('Product was updated.'));

        return redirect()->route('platform.products');
    }
}
