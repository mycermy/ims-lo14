<?php

namespace App\Orchid\Screens\Sales\Order;

use App\Models\Contact\Contact;
use Carbon\Carbon;
use App\Models\Product\Product;
use App\Models\Contact\Customer;
use Orchid\Screen\Screen;
use App\Models\Sales\SalesOrder;
use Illuminate\Http\Request;
use App\Models\Sales\SalesOrderItem;
use App\Orchid\Layouts\OrderListener;
use App\Orchid\Layouts\Orderv2Listener;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Components\Cells\Number;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use App\Orchid\Screen\TD;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class Order_EditScreen extends Screen
{
    public ?SalesOrder $order = null;
    public $orderItems;
    public $cartItems;
    protected $orderCartService;

    public function __construct()
    {
        $this->orderCartService = new CartService('order');
    }
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(SalesOrder $order): iterable
    {
        // dd($this->orderCartService->getCartItems());

        return [
            'order' => $order,
            // 'orderItems' => $order->orderItems()->get(),
            // 
            // 'orderItems' => Cart::instance('order')->content(),
            'cartItems' => $this->orderCartService->getCartItems(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->order->exists ? 'Edit ' . $this->order->reference : 'New Sales Order';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Create a new sales order';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add Order Detail')
                ->modal('xpressAddToCartModal')
                ->method('addOrderItem')
                // ->parameters([
                //     'contactType' => Contact::TYPE_CUSTOMER
                // ])
                ->icon('bs.window'),

            Button::make(__('Save'))
                ->icon('bs.check-circle')
                ->canSee(!$this->order->exists)
                ->method('store'),

            Link::make(__('Cancel'))
                ->icon('bs.x-circle')
                ->route('platform.orders'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        $currentYear = now()->year;
        $yearlyCount = SalesOrder::whereYear('created_at', $currentYear)->count() + 1;
        // Generate reference ID using your existing helper function
        $refid = make_reference_id('INV', $yearlyCount);

        return [
            Layout::split([
                Layout::rows([
                    Input::make('order.reference')
                        ->title('Reference')
                        ->readonly()
                        ->value($refid)
                        // Only required when saving the order
                        ->required($this->isStoringOrder()),

                    DateTimer::make('order.date')
                        ->title('Order Date')
                        ->format('d M Y')
                        ->value(now()->format('d M Y'))
                        ->allowInput()
                        // Only required when saving the order
                        ->required($this->isStoringOrder()),

                    Select::make('order.status')
                        ->title('Status')
                        ->options([
                            SalesOrder::STATUS_PENDING => SalesOrder::STATUS_PENDING,
                            SalesOrder::STATUS_APPROVED => SalesOrder::STATUS_APPROVED,
                        ]),
                ]),

                Layout::rows([
                    Relation::make('order.customer_id')
                        ->title('Customer')
                        ->fromModel(Contact::class, 'name', 'id')
                        ->applyScope('customer')
                        ->searchColumns('name', 'phone', 'email')
                        ->chunk(10)
                        // Only required when saving the order
                        ->required($this->isStoringOrder()),

                    TextArea::make('order.note')
                        ->title(__('Notes'))
                        ->rows(3),

                ]),
            ])->ratio('50/50'),

            Layout::modal('xpressAddToCartModal', Orderv2Listener::class)->title(__('Add New Sales Order Item')),

            // Layout::block(
            //     Orderv2Listener::class,
            // )
            //     ->title(__('Add New Item'))
            //     ->description(__('Add new item to order cart'))
            //     ->commands(
            //         Button::make(__('Add Item'))
            //             ->icon('bs.plus-circle')
            //             ->type(Color::BASIC)
            //             ->method('addOrderItem'),
            //     ),

            Layout::table('cartItems', [
                TD::make('id', '#')->render(fn($target, object $loop) => $loop->iteration + (getPage() - 1) * 0), //$target->getPerPage()),
                // TD::make('rowId'),
                TD::make('code')->render(fn($target) => Product::findOrFail($target->get('product_id'))->code ?? null),
                TD::make('name', 'Product'),
                TD::make('qty', __('Quantity'))->alignCenter(),
                TD::make('price', __('Unit Price'))->alignRight(),
                TD::make('subtotal', __('Sub Total'))->alignRight(), //->usingComponent(Number::class,['decimals'=>2]),
                TD::make('Actions')
                    ->canSee(Auth::user()->hasAnyAccess(['platform.systems.editor', 'platform.items.editor']))
                    ->width('10px')
                    ->render(
                        fn($target) =>
                        $this->getTableActions($target)
                            ->alignCenter()
                            ->autoWidth()
                            ->render()
                    ),
            ])->title(__('Sales Order Details')),
            // 
            // OrderListener::class,
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

            // DropDown::make()
            //     ->icon('three-dots-vertical')
            //     ->list([
            // Link::make(__('Edit'))
            //     ->icon('pencil')
            //     // ->canSee($this->can('update'))
            //     ->route('platform.orders.orderitem.edit', [$this->order, $target]),

            Button::make(__(''))
                ->icon('bs.trash3')
                // ->canSee($this->can('update'))
                // ->confirm(__('Once the account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.'))
                ->method('removeOrderItem', [
                    'rowId' => $target->get('rowId'),
                ]),
            // ]),
        ]);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, SalesOrder $order)
    {
        // kalo edit
        if ($this->order->exists) {
            $this->removeOldOrderDetails($order);
        }
        // 
        // Add a hidden input to indicate cart addition
        // $request->merge(['_store_order' => true]);
        // 
        $customer = Customer::findOrFail($request->input('order.customer_id'));

        $order->fill($request->get('order'));
        $order->fill([
            'date' => Carbon::parse($request->input('order.date'))->format('Y-m-d'),
            // 'date' => Carbon::parse($request->input('order.date'))->toDate(),
            'customer_name' => $customer->name,
            'updated_by' => auth()->id(),
        ]);
        $order->save();


        // $orderDetails = $request->get('orderItems');
        $orderDetails = $this->orderCartService->getCartItemsCollection();
        $totalAmount = $this->orderCartService->getPriceTotal() ?: 0;

        foreach ($orderDetails as $orderItem) {
            $product = Product::findOrFail($orderItem['product_id']);
            // $subTotal = $orderItem['quantity'] * $product->sell_price;

            // Create a new OrderDetail instance
            $newOrderDetail = new SalesOrderItem($orderItem);
            // $newOrderDetail->unit_price = $product->sell_price; // Set the unit_price attribute
            // $newOrderDetail->sub_total = $subTotal; // Set the sub_total attribute

            // Associate the new OrderDetail with the $order model
            $order->orderItems()->save($newOrderDetail);

            // Update stock quantity in the product
            if ($request->input('order.status') == SalesOrder::STATUS_APPROVED) {
                updateStock($orderItem['product_id'], $orderItem['quantity'], 'sub');
            }
            // 
            // $totalAmount += $orderItem['sub_total'];
        }

        // destroy cart order
        $this->orderCartService->destroyCart();

        $order->fill(['total_amount' => $totalAmount, 'due_amount' => $totalAmount])->save();

        // After saving the order, generate PDF
        // $this->generateOrderPDF($order);

        Toast::info(__('Sales order was saved.'));

        return redirect()->route('platform.orders.view', $order);
    }

    // Add a method to determine if we're adding to cart
    protected function isStoringOrder(): bool
    {
        return request()->has('_store_order');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeOldOrderDetails(SalesOrder $order)
    {
        $oldOrderStatus = $order->status ?? null;

        $oldOrderDetails = SalesOrderItem::where('order_id', $order->id)->get();

        foreach ($oldOrderDetails as $oldOrderItem) {
            // Update stock quantity in the product -> reverse
            if ($oldOrderStatus == SalesOrder::STATUS_APPROVED) {
                updateStock($oldOrderItem->product_id, $oldOrderItem->quantity, 'add');
            }

            $oldOrderItem->delete();
        }
    }
    /**
     * Add an item to the order.
     *
     * @param SalesOrder $order
     * @param int $productId
     * @param int $quantity
     * @param float $price
     */
    public function addOrderItem(Request $request)
    {
        $orderItem = $request->get('orderItem');
        $product = Product::find($orderItem['product_id']);

        // Add a hidden input to indicate cart addition
        $request->merge(['_add_to_cart' => true]);

        $this->orderCartService->addToCart(
            $product,
            $orderItem['product_name'],
            $orderItem['quantity'],
            $orderItem['unit_price']
        );

        // return redirect()->back();
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeOrderItem(Request $request)
    {
        $rowId = $request->get('rowId');
        $this->orderCartService->removeFromCart($rowId);

        // Add a hidden input to indicate cart addition
        $request->merge(['_drop_from_cart' => true]);

        Toast::info(__('Item was removed from cart.'));
        return redirect()->back();
    }

    // public function asyncCalculateTotal(Repository $repository): Repository
    // {
    //     return $repository;
    // }
}
