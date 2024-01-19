<?php

namespace App\Orchid\Screens\Product;

use App\Models\Category;
use App\Models\Product;
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

class DeletedProduct_ListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'model' => Product::onlyTrashed()->filters()->paginate(),
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
            Layout::table('model',[
                TD::make('id', '#')->render(fn ($target, object $loop) => $loop->iteration + (request('page') > 0 ? (request('page') - 1) * $target->getPerPage() : 0)),
                TD::make('category_id', 'Category')
                    ->filter(Relation::make()->fromModel(Category::class,'name'))
                    ->render(fn($target) => $target->category->name ?? null),
                TD::make('code')->filter()->sort(),
                TD::make('part_number', 'Part Number')->filter()->sort(),
                TD::make('name')->filter()->sort(),
                TD::make('sell_price', 'Sell Price')->alignRight(),
                TD::make('compatible')->filter()->sort(),
                // TD::make('created_by')->render(fn($target) => $target->createdBy->name),
                // TD::make('updated_by')->render(fn($target) => $target->updatedBy->name ?? null),
                TD::make('Actions')
                ->canSee(Auth::user()->hasAnyAccess(['platform.systems.editor','platform.items.editor']))
                ->width('10px')
                ->render(
                    fn ($target) =>
                    $this->getTableActions($target)
                        ->alignCenter()
                        ->autoWidth()
                        ->render()
                ),

            ]),
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
                    // Link::make(__('View'))
                    //     ->icon('eye')
                    //     // ->canSee($this->can('view'))
                    //     ->route('platform.sales.customers.view', $target),

                    Link::make(__('Edit'))
                        ->icon('pencil')
                        // ->canSee($this->can('update'))
                        ->route('platform.products.edit', $target),

                    Button::make(__('Restore'))
                        ->icon('bs.recycle')
                        ->confirm(__('Selected product to be retore.'))
                        ->method('restore', [
                            'id' => $target->id,
                        ])
                        ->canSee($target->trashed()),
                    //
                ]),
        ]);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore(Request $request): void
    {
        $productToRestore = Product::withTrashed()->findOrFail($request->get('id'));
        $productToRestore->restore();

        Toast::info(__('Product/Service was restored'));
    }
}
