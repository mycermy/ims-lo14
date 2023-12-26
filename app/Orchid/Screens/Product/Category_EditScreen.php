<?php

namespace App\Orchid\Screens\Product;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class Category_EditScreen extends Screen
{
    public $category;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Category $category): iterable
    {
        return [
            'category' => $category,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->category->exists ? 'Edit ' . $this->category->name : 'Create Category';
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
                ->canSee(!$this->category->exists)
                ->method('store'),

            Link::make(__('Cancel'))
                ->icon('bs.x-circle')
                ->route('platform.products.categories'),
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
            layout::block(Layout::rows([
                Relation::make('category.parent_id')
                    ->fromModel(Category::class, 'name')
                    ->title('Parent')
                    ->horizontal(),
                Input::make('category.name')
                    ->title('Name')
                    ->required()
                    ->horizontal(),
                Input::make('category.slug')
                    ->title('Slug')
                    ->required()
                    ->help('Slug must be unique')
                    ->horizontal(),

            ]))
            ->title('Category Details')
            ->description('Category Details Information')
            ->commands(
                Button::make(__('Update'))
                    ->type(Color::BASIC)
                    ->icon('bs.check-circle')
                    ->canSee($this->category->exists)
                    ->method('update')
            ),
        ];
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Category $category)
    {
        $request->validate([
            'category.slug' => [
                'required',
                Rule::unique(Category::class,'slug')
            ]
        ]);
        
        $category->fill($request->get('category'));
        $category->fill(['created_by' => auth()->id()]);

        $category->save();

        Toast::info(__('Category was saved.'));

        return redirect()->route('platform.products.categories');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Category $category)
    {
        $category->fill($request->get('category'));
        $category->fill(['updated_by' => auth()->id()]);

        $category->save();

        Toast::info(__('Category was updated.'));

        return redirect()->route('platform.products.categories');
    }
}
