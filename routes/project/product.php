<?php

use App\Models\Product;
use App\Orchid\Screens\Product\Category_EditScreen;
use App\Orchid\Screens\Product\Category_ListScreen;
use App\Orchid\Screens\Product\DeletedProduct_ListScreen;
use App\Orchid\Screens\Product\Product_EditScreen;
use App\Orchid\Screens\Product\Product_ListScreen;
use App\Orchid\Screens\Product\ProductHistory_ListScreen;
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

// Platfrom > Products > Categories
Route::screen('product/categories', Category_ListScreen::class)
    ->name('platform.products.categories')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Product Categories'), route('platform.products.categories')));

// Platfrom > Products > Categories > Create
Route::screen('product/categories/create', Category_EditScreen::class)
    ->name('platform.products.categories.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.products.categories')
        ->push(__('Create'), route('platform.products.categories.create')));

// Platfrom > Products > Categories > Edit
Route::screen('product/categories/{category}/edit', Category_EditScreen::class)
    ->name('platform.products.categories.edit')
    ->breadcrumbs(fn (Trail $trail, $category) => $trail
        ->parent('platform.products.categories')
        ->push(__($category->name), route('platform.products.categories.edit', $category)));

// Platfrom > Products
Route::screen('products', Product_ListScreen::class)
    ->name('platform.products')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Products'), route('platform.products')));

// Platfrom > Products > Create
Route::screen('products/create', Product_EditScreen::class)
    ->name('platform.products.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.products')
        ->push(__('Create'), route('platform.products.create')));

// Platfrom > Products > Edit
Route::screen('products/{product?}/edit', Product_EditScreen::class)
    ->name('platform.products.edit')
    ->breadcrumbs(fn (Trail $trail, $product) => $trail
        ->parent('platform.products')
        ->push(__($product->name), route('platform.products.edit')));

// Platfrom > Product > History
Route::screen('products/{product?}/view', ProductHistory_ListScreen::class)
    ->name('platform.product.hist')
    ->breadcrumbs(fn (Trail $trail, $product) => $trail
        ->parent('platform.products')
        ->push(__($product->name), route('platform.product.hist')));

// Platfrom > Products > Recycle Bin
Route::screen('deleted/products', DeletedProduct_ListScreen::class)
    ->name('platform.deleted.products')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.products')
        ->push(__('Deleted'), route('platform.deleted.products')));

