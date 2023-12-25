<?php

use App\Orchid\Screens\Product\Category_EditScreen;
use App\Orchid\Screens\Product\Category_ListScreen;
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
