<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// API-like resource routes for MVP scaffolding
use App\Http\Controllers\VendorController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WalletLedgerController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\OtpVerificationController;

Route::prefix('api')->group(function () {
    Route::apiResource('vendors', VendorController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('product-images', ProductImageController::class)->only(['index','store','show','destroy']);
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('order-items', OrderItemController::class)->only(['index','show']);
    Route::apiResource('payments', PaymentController::class)->only(['index','show','store']);
    Route::apiResource('wallets', WalletController::class)->only(['index','show']);
    Route::apiResource('wallet-ledger', WalletLedgerController::class)->only(['index','show']);
    Route::apiResource('payouts', PayoutController::class)->only(['index','show','store']);
    Route::post('otp-verifications', [OtpVerificationController::class, 'store']);
});
