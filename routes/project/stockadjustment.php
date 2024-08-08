<?php

use App\Orchid\Screens\Product\StockAdjustment_EditScreen;
use App\Orchid\Screens\Product\StockAdjustment_ListScreen;
use App\Orchid\Screens\Product\StockAdjustment_ViewScreen;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Platfrom > Products > Stock Adjustments
Route::screen('product/stockadjustments', StockAdjustment_ListScreen::class)
    ->name('platform.products.stockadjustments')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.products')
        ->push(__('Stock Adjustments'), route('platform.products.stockadjustments')));

// Platfrom > Products > Stock Adjustments > Create
Route::screen('product/stockadjustments/create', StockAdjustment_EditScreen::class)
    ->name('platform.products.stockadjustments.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.products.stockadjustments')
        ->push(__('Create'), route('platform.products.stockadjustments.create')));

// Platfrom > Products > Stock Adjustments > Edit
Route::screen('product/stockadjustments/{stockAdjustment?}/edit', StockAdjustment_EditScreen::class)
    ->name('platform.products.stockadjustments.edit')
    ->breadcrumbs(fn (Trail $trail, $stockAdjustment) => $trail
        ->parent('platform.products.stockadjustments')
        ->push(__($stockAdjustment->reference), route('platform.products.stockadjustments.edit')));
        // ->push(__($stockAdjustment->reference), route('platform.products.stockadjustments.edit', $stockAdjustment)));

// Platfrom > Products > Stock Adjustments > View
Route::screen('product/stockadjustments/{stockAdjustment?}/view', StockAdjustment_ViewScreen::class)
    ->name('platform.products.stockadjustments.view')
    ->breadcrumbs(fn (Trail $trail, $stockAdjustment) => $trail
        ->parent('platform.products.stockadjustments')
        ->push(__($stockAdjustment->reference), route('platform.products.stockadjustments.view')));
        // ->push(__($stockAdjustment->reference), route('platform.products.stockadjustments.view', $stockAdjustment)));

// // Platfrom > Products > Recycle Bin
// Route::screen('deleted/products', DeletedProduct_ListScreen::class)
//     ->name('platform.deleted.products')
//     ->breadcrumbs(fn (Trail $trail) => $trail
//         ->parent('platform.products')
//         ->push(__('Deleted'), route('platform.deleted.products')));

