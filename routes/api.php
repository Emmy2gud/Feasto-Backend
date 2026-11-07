<?php

use App\Http\Controllers\AdminActionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RiderController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\VendorVerificationController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WalletLedgerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/request-otp', [AuthController::class, 'requestOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Public App Settings & Discovery
Route::get('/settings/app', [SettingController::class, 'appSettings']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::get('/vendors', [VendorController::class, 'index']);
Route::get('/vendors/{id}', [VendorController::class, 'show']);
Route::get('/vendors/{vendor}/products', [ProductController::class, 'getProductsByVendor']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);


Route::post('/webhooks/korapay', [PaymentController::class, 'koraPayWebhook'])->name('webhooks.korapay');
// Route::match(['get', 'post'], '/webhooks/flutterwave', [PaymentController::class, 'koraPayWebhook'])->name('webhooks.flutterwave');

Route::middleware('auth:sanctum')->group(function () {

    // Current User & Profile
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);
    Route::get('/settings/user', [SettingController::class, 'userSettings']);
    Route::post('/settings/profile', [SettingController::class, 'updateProfile']);
    Route::post('/settings/password', [SettingController::class, 'changePassword']);

    // Vendors Management
    Route::post('/vendors', [VendorController::class, 'store']);
    Route::match(['put', 'patch'], '/vendors/{vendor}', [VendorController::class, 'update']);
    Route::delete('/vendors/{vendor}', [VendorController::class, 'destroy']);
    Route::post('/vendors/{vendor}/verify', [VendorVerificationController::class, 'verify']);

    // Products Management (Vendor/Admin)
    Route::post('/products', [ProductController::class, 'store']);
    Route::match(['put', 'patch'], '/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    Route::post('/products/images', [ProductImageController::class, 'store']);
    Route::delete('/products/images/{id}', [ProductImageController::class, 'destroy']);

    // Categories Management (Admin)
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::match(['put', 'patch'], '/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // Cart Management
    Route::get('/cart', [CartController::class, 'showCart']);
    Route::post('/cart/items', [CartController::class, 'addItem']);
    Route::post('/cart/add/{productId}', [CartController::class, 'addToCart']);
    Route::match(['put', 'patch'], '/cart/items/{id}', [CartController::class, 'updateQuantity']);
    Route::delete('/cart/items/{id}', [CartController::class, 'removeItem']);
    Route::delete('/cart', [CartController::class, 'clearCart']);

    // Checkout & Orders
    Route::get('/vendors/{vendor}/checkout-summary', [CheckoutController::class, 'summary']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'placeOrder']);
    Route::get('/orders/user', [OrderController::class, 'showUserOrders']);
    Route::get('/orders/vendor', [OrderController::class, 'showVendorOrders']);
    Route::get('/orders/all', [OrderController::class, 'showAllOrders']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::patch('/orders/{id}/accept', [OrderController::class, 'acceptOrder']);
    Route::patch('/orders/{id}/prepare', [OrderController::class, 'prepareOrder']);
    Route::patch('/orders/{id}/ready', [OrderController::class, 'readyOrder']);
    Route::patch('/orders/{id}/cancel', [OrderController::class, 'cancelOrder']);
    Route::get('/order-items/{id}', [OrderItemController::class, 'show']);

    // Rider Profile & Real-Time Tracking
    Route::get('/riders', [RiderController::class, 'index']);
    Route::get('/riders/{id}', [RiderController::class, 'show']);
    Route::post('/riders', [RiderController::class, 'store']);
    Route::patch('/rider/online', [RiderController::class, 'online']);
    Route::patch('/rider/location', [RiderController::class, 'locationUpdate']);

    // Rider Deliveries Workflow (MVP)
    Route::get('/rider/available-deliveries', [DeliveryController::class, 'available']);
    Route::get('/rider/active-delivery', [DeliveryController::class, 'active']);
    Route::get('/rider/history', [DeliveryController::class, 'history']);
    Route::get('/rider/earnings', [DeliveryController::class, 'earnings']);
    Route::post('/deliveries/accept', [DeliveryController::class, 'acceptDelivery']);
    Route::post('/deliveries/reject', [DeliveryController::class, 'rejectDelivery']);
    Route::patch('/deliveries/{order}/pickup', [DeliveryController::class, 'pickup']);
    Route::patch('/deliveries/{order}/complete', [DeliveryController::class, 'complete']);

    // Payments
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::get('/payments/{id}', [PaymentController::class, 'show']);
    Route::post('/payments/initiate', [PaymentController::class, 'initiate']);

    // Wallets, Ledger & Payouts
    Route::get('/wallet', [WalletController::class, 'index']);
    Route::get('/wallet/ledger', [WalletLedgerController::class, 'index']);
    Route::get('/payouts', [PayoutController::class, 'index']);
    Route::post('/payouts', [PayoutController::class, 'store']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    // Admin Actions
    Route::get('/admin/dashboard', [AdminActionController::class, 'dashboardStats']);
    Route::post('/admin/vendors/{vendor}/approve', [AdminActionController::class, 'approveVendor']);
    Route::post('/admin/riders/{rider}/approve', [AdminActionController::class, 'approveRider']);
    Route::post('/admin/riders/{rider}/reject', [AdminActionController::class, 'rejectRider']);
});
