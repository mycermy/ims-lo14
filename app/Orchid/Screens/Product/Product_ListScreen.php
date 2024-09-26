<?php

namespace App\Orchid\Screens\Product;

use App\Models\Product\Category;
use App\Models\Product\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class Product_ListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'model' => Product::filters()->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Products & Services';
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
                ->route('platform.products.create'),

            ModalToggle::make('Express Add')
                ->modal('xpressAddModal')
                ->method('store')
                ->icon('bs.window')
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
                TD::make('id', '#')->render(fn($target, object $loop) => $loop->iteration + (getPage() - 1) * $target->getPerPage()),
                TD::make('category_id', 'Category')
                    ->filter(Relation::make()->fromModel(Category::class, 'name'))
                    ->render(fn ($target) => $target->category->name ?? null)
                    ->width('auto')
                    ->class('text-break'),
                TD::make('code')->filter()->sort()
                    ->render(
                        function ($target) {
                            if ($target->code) {
                                return Link::make($target->code)
                                    ->route('platform.product.hist', $target);
                            } else {
                                return null;
                            }
                        }
                    ),
                TD::make('part_number', 'Part Number')->filter()->sort(),
                TD::make('name')->filter()->sort()->width('auto')->class('text-break'),
                TD::make('quantity')->alignRight(),
                TD::make('sell_price', 'Sell Price')->alignRight(),
                TD::make('compatible')->filter()->sort()->width('auto'),
                // TD::make('created_by')->render(fn($target) => $target->createdBy->name),
                // TD::make('updated_by')->render(fn($target) => $target->updatedBy->name ?? null),
                TD::make('Actions')
                    ->canSee(Auth::user()->hasAnyAccess(['platform.systems.editor', 'platform.items.editor']))
                    ->width('10px')
                    ->render(
                        fn ($target) =>
                        $this->getTableActions($target)
                            ->alignCenter()
                            ->autoWidth()
                            ->render()
                    ),

            ]),

            Layout::modal('xpressAddModal', Layout::rows([
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
            ]))->title('Create new product/service.'),
        ];
    }

    /**
     * @param Model $model
     *
     * @return Group
     */
    private function getTableActions($target): Group
    {
        return Group::make([

            DropDown::make()
                ->icon('three-dots-vertical')
                ->list([
                    Link::make(__('View'))
                        ->icon('eye')
                        // ->canSee($this->can('view'))
                        ->route('platform.product.hist', $target),

                    Link::make(__('Edit'))
                        ->icon('pencil')
                        // ->canSee($this->can('update'))
                        ->route('platform.products.edit', $target),

                    Button::make(__('Delete'))
                        ->icon('bs.trash3')
                        ->confirm(__('Once the product is deleted, all of its resources and data will be permanently deleted. Before deleting your product, please download any data or information that you wish to retain.'))
                        ->method('remove', [
                            'id' => $target->id,
                        ])
                        ->canSee(!$target->trashed()),
                ]),
        ]);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'product.code' => [
                'required',
                Rule::unique(Product::class, 'code')
            ]
        ]);

        $product->fill($request->get('product'));
        $product->fill(['name' => strtoupper($request->input('product.name'))]);
        $product->fill(['created_by' => auth()->id()]);

        $product->save();

        Toast::info(__('Product was saved.'));
    }

    public function remove(Request $request): void
    {
        $productToRemove = Product::findOrFail($request->get('id'));
        $productToRemove->delete();

        Toast::info(__('Product was removed'));
    }
}
