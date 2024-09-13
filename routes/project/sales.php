<?php

use App\Orchid\Screens\Sales\Order\Order_EditScreen;
use App\Orchid\Screens\Sales\Order\Order_ListScreen;
use App\Orchid\Screens\Sales\Order\Order_ViewScreen;
use App\Orchid\Screens\Sales\OrderPayment\OrderPayment_EditScreen;
use App\Orchid\Screens\Sales\OrderPayment\OrderPayment_ListScreen;
use App\Orchid\Screens\Sales\OrderReturn\OrderReturn_ListScreen;
use App\Orchid\Screens\Sales\OrderReturn\OrderReturnSingle_CreateScreen;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;


// Platfrom > Orders
Route::screen('orders', Order_ListScreen::class)
    ->name('platform.orders')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Orders'), route('platform.orders')));

// Platfrom > Orders > Create
Route::screen('orders/create', Order_EditScreen::class)
    ->name('platform.orders.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.orders')
        ->push(__('Create'), route('platform.orders.create')));

// Platfrom > Orders > Edit
Route::screen('orders/{order?}/edit', Order_EditScreen::class)
    ->name('platform.orders.edit')
    ->breadcrumbs(fn (Trail $trail, $order) => $trail
        ->parent('platform.orders')
        ->push(__($order->reference), route('platform.orders.edit')));

// Platfrom > Orders > View
Route::screen('orders/{order}/view', Order_ViewScreen::class)
    ->name('platform.orders.view')
    ->breadcrumbs(fn (Trail $trail, $order) => $trail
        ->parent('platform.orders')
        ->push(__($order->reference), route('platform.orders.view', $order)));

// 
// Platfrom > Order Returns
Route::screen('order-returns', Order_ListScreen::class)
    ->name('platform.orderreturns')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.orders')
        ->push(__('Order Returns'), route('platform.orderreturns')));

// 
// Platfrom > Orders > Returns
Route::screen('orders/{order?}/returns', OrderReturn_ListScreen::class)
    ->name('platform.orders.returns')
    ->breadcrumbs(fn (Trail $trail, $order) => $trail
        ->parent('platform.orders.view', $order)
        ->push(__('Return'), route('platform.orders.returns')));

// Platfrom > Orders > Returns > Create
Route::screen('orders/{order?}/create-return', Order_EditScreen::class)
    ->name('platform.orders.returns.create')
    ->breadcrumbs(fn (Trail $trail, $order) => $trail
        ->parent('platform.orders.view', $order)
        ->push(__('Create Return'), route('platform.orders.returns.create')));

// Platfrom > Orders > Return Single > Create
Route::screen('orders/{order?}/{orderDetail?}/create-return', OrderReturnSingle_CreateScreen::class)
    ->name('platform.orders.returnbyorderitem.create')
    ->breadcrumbs(fn (Trail $trail, $order, $orderItem) => $trail
        ->parent('platform.orders.view', $order)
        ->push(__('Create Return'), route('platform.orders.returnbyorderitem.create')));

// 
// Platfrom > Orders > Payments
Route::screen('orders/{order?}/payments', OrderPayment_ListScreen::class)
    ->name('platform.orders.payments')
    ->breadcrumbs(fn (Trail $trail, $order) => $trail
        ->parent('platform.orders.view', $order)
        ->push(__('Payments'), route('platform.orders.payments')));

// 
// Platfrom > Orders > Payments > Create
Route::screen('orders/{order?}/create-payment', OrderPayment_EditScreen::class)
    ->name('platform.orders.payments.create')
    ->breadcrumbs(fn (Trail $trail, $order) => $trail
        ->parent('platform.orders.view', $order)
        ->push(__('Create Payment'), route('platform.orders.payments.create')));

// Platfrom > Orders > Payments > Edit
Route::screen('orders/{order?}/payments/{payment?}/edit', OrderPayment_EditScreen::class)
    ->name('platform.orders.payments.edit')
    ->breadcrumbs(fn (Trail $trail, $order, $payment) => $trail
        ->parent('platform.orders.view', $order)
        ->push(__($payment->reference), route('platform.orders.payments.edit')));

//  