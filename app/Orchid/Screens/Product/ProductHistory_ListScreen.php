<?php

namespace App\Orchid\Screens\Product;

use Carbon\Carbon;
use Orchid\Screen\TD;
use App\Models\Product\Product;
use Orchid\Screen\Sight;
use Orchid\Screen\Screen;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Components\Cells\DateTimeSplit;

class ProductHistory_ListScreen extends Screen
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
            'stock_adj' => $product->adjustedProducts()->defaultSort('created_at', 'desc')->limit(10)->get(),
            'purchase_hist' => $product->purchaseDetails()->defaultSort('created_at', 'desc')->limit(10)->get(),
            'purchase_return_hist' => $product->purchaseReturnItems()->defaultSort('created_at', 'desc')->limit(10)->get(),
            'order_h' => $product->orders()->defaultSort('created_at', 'desc')->limit(10)->get(),
            // 'order_hist' => $product->orderItems()->defaultSort('created_at', 'desc')->limit(10)->get(),
            'order_return_hist' => $product->orderReturnItems()->defaultSort('created_at', 'desc')->limit(10)->get(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->product->name;
        // return 'Product History';
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return $this->product->is_active == false ? ' !! This Is Archived Item.' : 'Item Histories';
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
        // dd(date_default_timezone_get());

        return [
            Layout::columns([
                Layout::legend('product', [
                    Sight::make('is_active', __('Item Status'))->render(fn($target) => $this->productAvailability($target)),
                    Sight::make('quantity', __('Available Qty'))->render(fn($target) => $target->quantity < $target->quantity_alert
                        ? '<b class="text-danger">' . $target->quantity . '</b>'
                        : '<b class="text-success">' . $target->quantity . '</b>'),
                    Sight::make('quantity_alert', __('Alert Qty')),
                    // Sight::make('is_autoreset',__('Item Type'))->popover(__('Stock Item / Service Item'))
                    //     ->render(fn(Item $item) => $item->is_autoreset == true
                    //     ? '<i class="text-danger">●</i> Service Item'
                    //     : '<i class="text-success">●</i> Stock Item'),
                ]),

                Layout::legend('product', [
                    // Sight::make('income_category_id', __('Sales Category'))->render(fn($target) => $target->catincome->name),
                    // Sight::make('expense_category_id',__('Expense Category'))->render(fn($target) => $target->catexpense->name),
                    Sight::make('category_id', __('Item Category'))->render(fn($target) => $target->category->name),
                ]),
            ]),

            Layout::table('stock_adj', [
                TD::make('date')->width(150)
                    ->render(fn($target) => $this->dateTimeSplit($target->adjustment->created_at)),
                TD::make('stock_adjustment_id', 'Reference')
                    ->render(
                        fn($target) =>
                        Link::make($target->adjustment->reference)
                            ->route('platform.products.stockadjustments.view', $target->adjustment)
                    ),
                TD::make('quantity'),
                TD::make('type'),
            ])->title('Stock Adjustment History'),

            Layout::table('purchase_hist', [
                TD::make('date')->width(150)
                    ->render(fn($target) => $this->dateTimeSplit($target->purchase->created_at)),
                TD::make('purchase_id', 'Reference')
                    // ->render(fn($target) => $target->purchase->reference),
                    ->render(
                        fn($target) =>
                        Link::make($target->purchase->reference)
                            ->route('platform.purchases.view', $target->purchase)
                    ),
                TD::make('quantity')->alignCenter()->width(50),
                TD::make('unit_price', 'Unit Price')->alignRight()->width(100),
                TD::make('sub_total', 'Sub Total')->alignRight()->width(150),
            ])->title('Purchase History'),

            Layout::table('purchase_return_hist', [
                TD::make('date')->width(150)
                    ->render(fn($target) => $this->dateTimeSplit($target->purchaseReturn->created_at)),
                // TD::make('created_at')
                //     ->render(fn($target) => $target->created_at), // created_at jadi localtime macam dalam recorded db
                // TD::make('created_at')
                //     ->usingComponent(DateTimeSplit::class), // created_at jadi UTC timezone
                TD::make('purchase_return_id', 'Reference')
                    // ->render(fn($target) => $target->purchase->reference),
                    ->render(
                        fn($target) =>
                        Link::make($target->purchaseReturn->reference)
                            ->route('platform.purchases.returns', $target->purchaseReturn->purchase)
                    ),
                TD::make('quantity')->alignCenter()->width(50),
                TD::make('unit_price', 'Unit Price')->alignRight()->width(100)
                    ->render(fn($target) => $target->purchaseDetail->unit_price),
                TD::make('sub_total', 'Sub Total')->alignRight()->width(150),
            ])->title('Purchase Return History'),

            Layout::table('order_h', [
                TD::make('date')->width(150) //->usingComponent(dateTimeSplit::class),
                    ->render(fn($target) => $this->dateTimeSplit($target->created_at)),
                TD::make('reference', 'Reference')
                    // ->render(fn($target) => $target->purchase->reference),
                    ->render(
                        fn($target) =>
                        Link::make($target->reference)
                            ->route('platform.orders.view', $target)
                    ),
                TD::make('pivot_quantity')->alignCenter()->width(50)
                    ->render(fn($target) => $target->pivot->quantity),
                TD::make('pivot_unit_price', 'Unit Price')->alignRight()->width(100)
                    ->render(fn($target) => $target->pivot->unit_price),
                TD::make('pivot_sub_total', 'Sub Total')->alignRight()->width(150)
                    ->render(fn($target) => $target->pivot->sub_total),
            ])->title('Order History'),

            // Layout::table('order_hist', [
            //     TD::make('date')->width(150)
            //         ->render(fn($target) => $this->dateTimeSplit($target->order->created_at)),
            //     TD::make('order_id', 'Reference')
            //         ->render(
            //             fn($target) =>
            //             Link::make($target->order->reference)
            //                 ->route('platform.orders.view', $target->order)
            //         ),
            //     TD::make('quantity')->alignCenter()->width(50),
            //     TD::make('unit_price', 'Unit Price')->alignRight()->width(100),
            //     TD::make('sub_total', 'Sub Total')->alignRight()->width(150),
            // ])->title('Order History'),

            Layout::table('order_return_hist', [
                TD::make('date')->width(150)
                    ->render(fn($target) => $this->dateTimeSplit($target->orderReturn->created_at)),
                TD::make('order_id', 'Reference')
                    ->render(
                        fn($target) =>
                        Link::make($target->orderReturn->reference)
                            ->route('platform.orders.returns', $target->orderReturn->order)
                    ),
                TD::make('quantity')->alignCenter()->width(50),
                TD::make('unit_price', 'Unit Price')->alignRight()->width(100)
                    ->render(fn($target) => $target->orderItem->unit_price),
                TD::make('sub_total', 'Sub Total')->alignRight()->width(150),
            ])->title('Order Return History'),
            // 
        ];
    }

    public function productAvailability($target)
    {
        $code = '';
        if ($target->is_active) {
            $code = '<span class="badge text-bg-success text-white text-uppercase">Active</span>';
            if ($target->quantity <= 0) {
                $code .= '<span class="badge text-bg-danger text-uppercase ms-2">Zero Stock</span>';
            } else if ($target->quantity < $target->quantity_alert) {
                $code .= '<span class="badge text-bg-danger text-uppercase ms-2">Low Stock</span>';
            } else if ($target->quantity == $target->quantity_alert) {
                $code .= '<span class="badge text-bg-warning text-uppercase ms-2">Low Stock</span>';
            }
        } else {
            $code = '<span class="badge text-bg-danger text-uppercase">ARCHIVED</span>';
        }

        return $code;;
    }

    public function dateTimeSplit($value)
    {
        $upperFormat = 'M j, Y';
        $lowerFormat = 'D, H:i';
        $tz = null;


        $date = Carbon::parse($value, $tz);

        return sprintf(
            '<time class="mb-0 text-capitalize">%s<span class="text-muted d-block">%s</span></time>',
            $date->translatedFormat($upperFormat),
            $date->translatedFormat($lowerFormat),
        );
    }
}
