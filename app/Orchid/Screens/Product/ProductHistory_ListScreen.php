<?php

namespace App\Orchid\Screens\Product;

use App\Models\Product;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Screen;
use Orchid\Screen\Sight;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

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
            'purchase_hist' => $product->purchaseDetails()->defaultSort('created_at', 'desc')->limit(10)->get(),
            'stock_adj' => $product->adjustedProducts()->defaultSort('created_at', 'desc')->limit(10)->get(),
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
                TD::make('date')
                    ->render(fn($target) => $target->adjustment->date),
                // ->usingComponent(DateTimeSplit::class),
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
                TD::make('date')
                    ->render(fn($target) => $target->purchase->date),
                // ->usingComponent(DateTimeSplit::class),
                TD::make('purchase_id', 'Reference')
                    // ->render(fn($target) => $target->purchase->reference),
                    ->render(
                        fn($target) =>
                        Link::make($target->purchase->reference)
                            ->route('platform.purchases.view', $target->purchase)
                    ),
                TD::make('quantity'),
                TD::make('unit_price', 'Unit Price'),
                TD::make('sub_total', 'Sub Total'),
            ])->title('Purchase History'),
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
        
        return $code;                        ;
    }
}
