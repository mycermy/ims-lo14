<?php

namespace App\Orchid\Screens\Product;

use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class StockAdjustment_ListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(StockAdjustment $model): iterable
    {
        $model->newQuery()->filters();
        // $model->newQuery()->withCount('adjustedProducts');
        // dd($model->paginate());
        return [
            // 'model' => Adjustment::paginate(),
            'model' => $model->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Stock Adjustment List';
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
                ->route('platform.products.stockadjustments.create'),
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
                TD::make('date'),
                TD::make('reference'),
                TD::make('note')->width('auto'),
                TD::make('adjusted_products_count', 'ProdCount')->alignCenter(),
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
                    Link::make(__('View'))
                        ->icon('eye')
                        // ->canSee($this->can('view'))
                        ->route('platform.products.stockadjustments.view', $target),

                    Link::make(__('Edit'))
                        ->icon('pencil')
                        // ->canSee($this->can('update'))
                        ->route('platform.products.stockadjustments.edit', $target),

                    Button::make(__('Delete'))
                        ->icon('bs.trash3')
                        ->confirm(__('Once the product is deleted, all of its resources and data will be permanently deleted. Before deleting your product, please download any data or information that you wish to retain.'))
                        ->method('remove', [
                            'id' => $target->id,
                        ])
                        // ->canSee(!$target->trashed())
                        ,
                ]),
        ]);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Request $request)
    {
        $stockAdjustment = StockAdjustment::findOrFail($request->get('id'));

        // child
        foreach ($stockAdjustment->adjustedProducts as $existingAdjustedProduct) {
            // get current stock
            $product = Product::findOrFail($existingAdjustedProduct['product_id']);
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

            // $existingAdjustedProduct->delete();
            
        }

        // parent
        $stockAdjustment->delete();

        Toast::info(__('Stock adjustment was deleted.'));
    }
    // 
}
