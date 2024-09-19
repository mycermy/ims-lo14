<?php

namespace App\Orchid\Layouts;

use App\Models\Contact;
use App\Models\Product;
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

class OrderListener extends Listener
{
    /**
     * List of field names for which values will be listened.
     *
     * @var string[]
     */
    protected $targets = [
        // 'minuend',
        // 'subtrahend',
        'orderItems.[{index}].[quantity]',
        'orderItems.0.quantity',
        'order.customer_id',
        'order.note',
        'order.status',
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
        $number = Order::max('id') + 1;
        $refid = make_reference_id('INV', $number);
        // $harini = now()->toDateString(); //dd($harini);
        $harini = now()->format('d M Y'); //dd($harini);

        return [
            // Layout::rows([
            //     Input::make('minuend')
            //         ->title('First argument')
            //         ->type('number'),

            //     Input::make('subtrahend')
            //         ->title('Second argument')
            //         ->type('number'),

            //     Input::make('result')
            //         ->readonly(),
            //         // ->canSee($this->query->has('result')),
            // ]),

            Layout::rows([
                Group::make([
                    Input::make('order.reference')
                        ->title('Reference')
                        ->required()
                        ->value($refid)
                        ->readonly(),
                    //
                    DateTimer::make('order.date')
                        ->title('Date')
                        ->format('d M Y')
                        ->required()
                        ->value($harini)
                        ->allowInput(),
                    //
                    Relation::make('order.customer_id')
                        ->title('Customer')
                        ->fromModel(Contact::class, 'name')
                        ->applyScope('customer')
                        ->searchColumns('name', 'phone', 'email')
                        ->chunk(10)
                        ->required(),
                ])->fullWidth(),
                //
                TextArea::make('order.note')
                    ->title('Note (If Needed)')
                    ->rows(3)
                    ->horizontal(),
                // 
                Matrix::make('orderItems')
                    ->title('Order Details')
                    ->removeableRows(false)
                    ->columns(['id', 'Product' => 'product_id', 'quantity', 'Unit Price' => 'unit_price', 'sub_total'])
                    ->fields([
                        'id' => Input::make('id')->readonly()->type('hidden'),
                        'product_id' => Relation::make('product_id')->fromModel(Product::class, 'name')->readonly()->searchColumns('name', 'code', 'part_number')->chunk(10)->required(),
                        'quantity' => Input::make('quantity')->type('number')->required(),
                        'unit_price' => Input::make('unit_price')->readonly(),
                        'sub_total' => Input::make('sub_total')->readonly(),
                    ]),
                //
                Input::make('orderTotal')
                    ->title('Total')
                    ->readonly()
                    ->horizontal(),
                //
                Select::make('order.status')
                    ->title('Order Status')
                    ->options([
                        Order::STATUS_PENDING => Order::STATUS_PENDING,
                        Order::STATUS_APPROVED => Order::STATUS_APPROVED,
                    ])
                    // ->empty('No select')
                    ->horizontal(),
            ]),
        ];
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
        $minuend = $request->input('minuend');
        $subtrahend = $request->input('subtrahend');

        $items = $request->get('orderItems', []);
        $modifiedItems = [];
        $totalAmount = 0;

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if ($product) {
                $quantity = floatval($item['quantity'] ?? 0);
                $price = floatval($product->sell_price ?? 0);
                $subTotal = $quantity * $price;
                
                $item['unit_price'] = number_format($price, 2);
                $item['sub_total'] = number_format($subTotal, 2);
                $modifiedItems[] = $item;
                
                $totalAmount += $subTotal;
            }
        }

        return $repository
            // ->set('minuend', $minuend)
            // ->set('subtrahend', $subtrahend)
            // ->set('result', $minuend - $subtrahend)
            ->set('orderItems', array_values($modifiedItems))
            ->set('orderTotal', number_format($totalAmount, 2))
            ->set('order.customer_id', $request->input('order.customer_id'))
            ->set('order.status', $request->input('order.status'))
            ;
    }
}
