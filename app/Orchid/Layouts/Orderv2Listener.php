<?php

namespace App\Orchid\Layouts;

use App\Models\Contact\Contact;
use App\Models\Product\Product;
use App\Models\Sales\Order;
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

class Orderv2Listener extends Listener
{
    /**
     * List of field names for which values will be listened.
     *
     * @var string[]
     */
    protected $targets = [
        'orderItem.product_id',
    ];

    // /**
    //  * What screen method should be called
    //  * as a source for an asynchronous request.
    //  *
    //  * @var string
    //  */
    // protected $asyncMethod = 'asyncCalculateTotal';


    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    protected function layouts(): iterable
    {
        return [
            Layout::rows([
                Relation::make('orderItem.product_id')
                    ->fromModel(Product::class, 'name')
                    ->searchColumns('name', 'code', 'part_number')
                    ->chunk(10)
                    // ->empty('No select')
                    ->title(__('Product'))
                    // Remove required() when not adding to cart
                    ->required($this->isAddingToCart()),

                Input::make('orderItem.product_name')
                    ->title('Product Name Custom'),

                Input::make('orderItem.quantity')
                    ->type('number')
                    ->title(__('Quantity'))
                    ->min(1)
                    // Make conditional based on cart addition
                    ->required($this->isAddingToCart()),

                Input::make('orderItem.unit_price')
                    ->type('number')
                    ->title(__('Price'))
                    // Make conditional based on cart addition
                    ->required($this->isAddingToCart()),
            ]),
        ];
    }

    // Add a method to determine if we're adding to cart
    protected function isAddingToCart(): bool
    {
        return request()->has('_add_to_cart');
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
        $item = $request->get('orderItem');
        $quantity = 0;
        $price = 0;
        // $modifiedItem = [];

        // $product = Product::find($request->input('orderItem.product_id'));
        $product = Product::find($item['product_id']);
        if ($product) {
            $quantity = floatval($item['quantity'] ?? 1);
            $price = floatval($product->sell_price ?? 0);

            // $item['quantity'] = number_format($quantity);
            // $item['unit_price'] = number_format($price, 2);
            // $modifiedItem[] = $item;
        }

        return $repository
            ->set('orderItem.product_id', $request->input('orderItem.product_id'))
            ->set('orderItem.product_name', $product->name)
            ->set('orderItem.quantity', number_format($quantity))
            ->set('orderItem.unit_price',  number_format($price, 2))
            // ->set('order.customer_id', $request->input('order.customer_id'))
            // ->set('order.note', $request->input('order.note'))
        ;
    }
}
