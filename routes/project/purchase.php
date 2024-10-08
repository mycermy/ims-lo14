<?php

use App\Orchid\Screens\Contact\DeletedContact_ListScreen;
use App\Orchid\Screens\Purchase\Bill\Bill_EditScreen;
use App\Orchid\Screens\Purchase\Bill\Bill_ListScreen;
use App\Orchid\Screens\Purchase\Bill\Bill_ViewScreen;
use App\Orchid\Screens\Purchase\BillPayment\BillPayment_EditScreen;
use App\Orchid\Screens\Purchase\BillPayment\BillPayment_ListScreen;
use App\Orchid\Screens\Purchase\BillReturn\BillReturn_EditScreen;
use App\Orchid\Screens\Purchase\BillReturn\BillReturn_ListScreen;
use App\Orchid\Screens\Purchase\BillReturn\BillReturnSingle_CreateScreen;
use App\Orchid\Screens\Purchase\PurchasePayments_ListScreen;
use App\Orchid\Screens\Purchase\PurchaseReturns_ListScreen;
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
Route::screen('purchases/{purchase}/view', Bill_ViewScreen::class)
    ->name('platform.purchases.view')
    ->breadcrumbs(fn (Trail $trail, $purchase) => $trail
        ->parent('platform.purchases')
        ->push(__($purchase->reference), route('platform.purchases.view', $purchase)));

// Platfrom > Purchase Returns
Route::screen('purchase-returns', PurchaseReturns_ListScreen::class)
    ->name('platform.purchasereturns')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.purchases')
        ->push(__('Purchase Returns'), route('platform.purchasereturns')));

// Platfrom > Purchases > Returns
Route::screen('purchases/{purchase?}/returns', BillReturn_ListScreen::class)
    ->name('platform.purchases.returns')
    ->breadcrumbs(fn (Trail $trail, $purchase) => $trail
        ->parent('platform.purchases.view', $purchase)
        ->push(__('Return'), route('platform.purchases.returns')));

// Platfrom > Purchases > Returns > Create
Route::screen('purchases/{purchase?}/create-return', BillReturn_EditScreen::class)
    ->name('platform.purchases.returns.create')
    ->breadcrumbs(fn (Trail $trail, $purchase) => $trail
        ->parent('platform.purchases.view', $purchase)
        ->push(__('Create Return'), route('platform.purchases.returns.create')));

// Platfrom > Purchases > Return Single > Create
Route::screen('purchases/{purchase?}/{purchaseDetail?}/create-return', BillReturnSingle_CreateScreen::class)
    ->name('platform.purchases.returnbypurchasedetails.create')
    ->breadcrumbs(fn (Trail $trail, $purchase, $purchaseDetail) => $trail
        ->parent('platform.purchases.view', $purchase)
        ->push(__('Create Return'), route('platform.purchases.returnbypurchasedetails.create')));

// Platfrom > Purchases > Deleted
Route::screen('deleted/purchases', DeletedContact_ListScreen::class)
    ->name('platform.deleted.purchases')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.purchases')
        ->push(__('Deleted'), route('platform.deleted.purchases')));

// Platfrom > Purchase Payments
Route::screen('purchase-payments', PurchasePayments_ListScreen::class)
    ->name('platform.purchasepayments')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.purchases')
        ->push(__('Purchase Payment'), route('platform.purchasepayments')));

// Platfrom > Purchases > Payments
Route::screen('purchases/{purchase?}/payments', BillPayment_ListScreen::class)
    ->name('platform.purchases.payments')
    ->breadcrumbs(fn (Trail $trail, $purchase) => $trail
        ->parent('platform.purchases.view', $purchase)
        ->push(__('Payments'), route('platform.purchases.payments')));

// Platfrom > Purchases > Payments > Create
Route::screen('purchases/{purchase?}/create-payment', BillPayment_EditScreen::class)
    ->name('platform.purchases.payments.create')
    ->breadcrumbs(fn (Trail $trail, $purchase) => $trail
        ->parent('platform.purchases.view', $purchase)
        ->push(__('Create Payment'), route('platform.purchases.payments.create')));

// Platfrom > Purchases > Payments > Edit
Route::screen('purchases/{purchase?}/payments/{payment?}/edit', BillPayment_EditScreen::class)
    ->name('platform.purchases.payments.edit')
    ->breadcrumbs(fn (Trail $trail, $purchase, $payment) => $trail
        ->parent('platform.purchases.view', $purchase)
        ->push(__($payment->reference), route('platform.purchases.payments.edit')));

// // Platfrom > Purchases > Payments > View
// Route::screen('purchases/{purchase?}/payments/{payment?}/view', Bill_ViewScreen::class)
//     ->name('platform.purchases.payments.view')
//     ->breadcrumbs(fn (Trail $trail, $purchase, $payment) => $trail
//         ->parent('platform.purchases', $purchase)
//         ->push(__($payment->reference), route('platform.purchases.payments.view')));

//
