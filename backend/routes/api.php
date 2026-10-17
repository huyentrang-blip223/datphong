<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::get('/payment/vn-pay-ipn', [PaymentController::class, 'vnPayIpn'])->name('api.v1.payment.vnpay-ipn');
    Route::get('/payment/vn-pay-callback', [PaymentController::class, 'vnPayCallback'])->name('api.v1.payment.vnpay-callback');
});
