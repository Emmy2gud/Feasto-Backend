<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\VendorVerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// OTP endpoints
Route::post('/request-otp', [AuthController::class, 'requestOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

// Auth endpoints
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('vendors', VendorController::class);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::post('/vendors/{vendor}/verify', [VendorVerificationController::class, 'verify']);


//category endpoints
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products', \App\Http\Controllers\ProductController::class);

    Route::post('cart/add/{productId}', [CartController::class, 'addToCart']);
    Route::get('/cart', [CartController::class, 'showCart']);
    Route::post('/cart/items', [CartController::class, 'addItem']);
    Route::post('/cart/update/{courseId}', [CartController::class, 'updateCart'])->name('cart.update');
    Route::delete('/cart/{course}', [CartController::class, 'removeFromCart']);
    Route::post('/cart/sync', [CartController::class, 'syncCart']);
   Route::get('/vendors/{vendor}/checkout-summary', [CheckoutController::class, 'summary']);
   Route::post('/orders', [CheckoutController::class, 'placeOrder']);
    Route::post('/payments/initiate', [PaymentController::class, 'initiate']);
    Route::get('/webhooks/flutterwave', [PaymentController::class, 'FlutterwaveWebhook'])
        ->name('webhooks.flutterwave');
});
