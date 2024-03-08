<?php

use App\Orchid\Screens\Contact\Address_ListScreen;
use App\Orchid\Screens\Contact\Contact_EditScreen;
use App\Orchid\Screens\Contact\DeletedContact_ListScreen;
use App\Orchid\Screens\Purchase\Bill_EditScreen;
use App\Orchid\Screens\Purchase\Bill_ListScreen;
use App\Orchid\Screens\Purchase\Bill_ViewScreen;
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

// Platfrom > Purchases
Route::screen('purchases', Bill_ListScreen::class)
    ->name('platform.purchases')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Purchases'), route('platform.purchases')));

// Platfrom > Purchases > Create
Route::screen('purchases/create', Bill_EditScreen::class)
    ->name('platform.purchases.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.purchases')
        ->push(__('Create'), route('platform.purchases.create')));

// Platfrom > Purchases > Edit
Route::screen('purchases/{purchase?}/edit', Bill_EditScreen::class)
    ->name('platform.purchases.edit')
    ->breadcrumbs(fn (Trail $trail, $purchase) => $trail
        ->parent('platform.purchases')
        ->push(__($purchase->reference), route('platform.purchases.edit')));

// Platfrom > Purchases > View
Route::screen('purchases/{purchase?}/view', Bill_ViewScreen::class)
    ->name('platform.purchases.view')
    ->breadcrumbs(fn (Trail $trail, $purchase) => $trail
        ->parent('platform.purchases')
        ->push(__($purchase->reference), route('platform.purchases.view')));

// Platfrom > Purchase Returns
Route::screen('purchase-returns', Bill_ListScreen::class)
    ->name('platform.purchases.return')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.purchases')
        ->push(__('Purchase Returns'), route('platform.purchases.return')));

// Platfrom > Purchases > Deleted
Route::screen('deleted/purchases', DeletedContact_ListScreen::class)
    ->name('platform.deleted.purchases')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.purchases')
        ->push(__('Deleted'), route('platform.deleted.purchases')));

// // Platfrom > Purchases > Purchase Item Details
// Route::screen('purchases/billitems', Address_ListScreen::class)
//     ->name('platform.contacts.addresses')
//     ->breadcrumbs(fn (Trail $trail) => $trail
//         ->parent('platform.contacts')
//         ->push(__('Addresses'), route('platform.contacts.addresses')));

// // Platfrom > Products > Create
// Route::screen('products/create', Product_EditScreen::class)
//     ->name('platform.products.create')
//     ->breadcrumbs(fn (Trail $trail) => $trail
//         ->parent('platform.products')
//         ->push(__('Create'), route('platform.products.create')));

// // Platfrom > Products > Edit
// Route::screen('products/{product}/edit', Product_EditScreen::class)
//     ->name('platform.products.edit')
//     ->breadcrumbs(fn (Trail $trail, $product) => $trail
//         ->parent('platform.products')
//         ->push(__($product->name), route('platform.products.edit', $product)));
