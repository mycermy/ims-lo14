<?php

use App\Orchid\Screens\Contact\Address_ListScreen;
use App\Orchid\Screens\Contact\Contact_EditScreen;
use App\Orchid\Screens\Contact\Contact_ListScreen;
use App\Orchid\Screens\Contact\DeletedContact_ListScreen;
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

// Platfrom > Contacts
Route::screen('contacts', Contact_ListScreen::class)
    ->name('platform.contacts')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Contacts'), route('platform.contacts')));

// Platfrom > Contacts > Create
Route::screen('contacts/create', Contact_EditScreen::class)
    ->name('platform.contacts.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.contacts')
        ->push(__('Create'), route('platform.contacts.create')));

// Platfrom > Contacts > Edit
Route::screen('contacts/{contact?}/edit', Contact_EditScreen::class)
    ->name('platform.contacts.edit')
    ->breadcrumbs(fn (Trail $trail, $contact) => $trail
        ->parent('platform.contacts')
        ->push(__($contact->name), route('platform.contacts.edit')));

// Platfrom > Contacts > Deleted
Route::screen('deleted/contacts', DeletedContact_ListScreen::class)
    ->name('platform.deleted.contacts')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.contacts')
        ->push(__('Deleted'), route('platform.deleted.contacts')));

// Platfrom > Contacts > Addresses
Route::screen('contact/addresses', Address_ListScreen::class)
    ->name('platform.contacts.addresses')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.contacts')
        ->push(__('Addresses'), route('platform.contacts.addresses')));

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
