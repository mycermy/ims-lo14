<?php

namespace App\Orchid\Screens\Purchase;

use App\Models\Product;
use Orchid\Screen\TD;
use App\Models\Purchase;
use Orchid\Screen\Screen;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Layout;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Fields\Group;
use Orchid\Support\Facades\Toast;

class Bill_ListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'model' => Purchase::filters()->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Bills Listing';
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
                ->route('platform.purchases.create'),
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
            Layout::table('model',[
                TD::make('updated_at', 'Date'),
                TD::make('reference'),
                TD::make('supplier_name', 'Supplier'),
                TD::make('status')->alignCenter(),
                TD::make('total_amount')->alignRight(),
                TD::make('paid_amount')->alignRight(),
                TD::make('due_amount')->alignRight(),
                TD::make('payment_status')->alignCenter(),
                TD::make('Actions')->alignCenter()
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
                        ->route('platform.purchases.view', $target),

                    Link::make(__('Edit'))
                        ->icon('pencil')
                        // ->canSee($this->can('update'))
                        ->route('platform.purchases.edit', $target),

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
        $purchase = Purchase::findOrFail($request->get('id'));

        // child
        foreach ($purchase->purchaseDetails as $existingPurchaseDetail) {
            // get current stock
            $product = Product::findOrFail($existingPurchaseDetail['product_id']);
            // rollback stock qty in product table
            $newQty = $product->quantity - $existingPurchaseDetail['quantity'];
            $product->update([
                'quantity' => $newQty
            ]);

            // $existingAdjustedProduct->delete();

            Toast::warning(__('Product Rollback. (- ' . $existingPurchaseDetail['quantity'] . ') => ' . $newQty . ' ' . $product->name));
            
        }

        // parent
        $purchase->delete();

        Toast::info(__('Purchase was deleted.'));
    }
    // 
        
}
