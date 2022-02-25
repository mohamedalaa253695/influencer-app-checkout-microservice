<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

    Route::get('links/{code}', [CheckoutLinkController::class, 'show']);
    Route::post('orders', [CheckoutOrderController::class, 'store']);
    Route::post('orders/confirm', [CheckoutOrderController::class, 'confirm']);
