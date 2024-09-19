<?php

namespace App\Orchid\Screens\Product\Category;

use App\Models\Product\Category;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class Category_ListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'model' => Category::withCount('products')->filters()->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Product Categories';
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
                ->route('platform.products.categories.create'),
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
                TD::make('id', '#')->render(fn ($target, object $loop) => $loop->iteration + (request('page') > 0 ? (request('page') - 1) * $target->getPerPage() : 0)),
                TD::make('parent_id')
                ->filter(Relation::make()->fromModel(Category::class,'name'))
                ->render(fn($target) => $target->parent->name ?? null),
                TD::make('name'),
                TD::make('products_count')->alignCenter(),
                TD::make('created_by')->render(fn($target) => $target->createdBy->name),
                TD::make('updated_by')->render(fn($target) => $target->updatedBy->name ?? null),
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
                        ->route('platform.products.categories.edit', $target->slug),
                ]),
        ]);
    }
}
