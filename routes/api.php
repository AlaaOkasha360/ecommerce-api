<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])->middleware('verified.api');
    Route::middleware(['auth:api', 'verified.api'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::group(['middleware' => ['auth:api', 'verified.api'], 'prefix' => 'users'], function () {
    Route::get('/profile', [UserController::class, 'show_profile']);
    Route::put('/profile', [UserController::class, 'update_profile']);
    Route::get('/addresses', [UserController::class, 'show_addresses']);
    Route::post('/addresses', [UserController::class, 'create_address']);
    Route::put('/addresses/{address}', [UserController::class, 'update_address']);
    Route::delete('/addresses/{address}', [UserController::class, 'delete_address']);
    Route::get('/orders', [UserController::class, 'index_orders']);
    Route::get('/orders/{order}', [UserController::class, 'show_order']);
});

Route::group(['middleware' => ['auth:api', 'admin'], 'prefix' => 'admin'], function () {
    Route::get('/users', [AdminController::class, 'index']);
    Route::get('/users/{user}', [AdminController::class, 'show']);
    Route::put('/users/{user}', [AdminController::class, 'update']);
    Route::delete('/users/{user}', [AdminController::class, 'destroy']);
});

// Public product routes
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::post('/', [ProductController::class, 'store'])->middleware(['auth:api', 'admin']);
    Route::get('/search', [ProductController::class, 'search']);
    Route::get('/category/{slug}', [ProductController::class, 'product_category']);
    Route::get('/{product}', [ProductController::class, 'show']);
    Route::put('/{product}', [ProductController::class, 'update'])->middleware(['auth:api', 'admin']);
    Route::delete('/{product}', [ProductController::class, 'destroy'])->middleware(['auth:api', 'admin']);
    Route::get('/{product}/reviews', [ProductController::class, 'product_reviews']);
    Route::post('/{product}/reviews', [ProductController::class, 'store_review'])->middleware('auth:api', 'verified.api');

});

// Category routes
Route::prefix('categories')->group(function () {
    Route::get('', [CategoriesController::class, 'index']);
    Route::get('/{category}', [CategoriesController::class, 'show']);
    Route::post('', [CategoriesController::class, 'store'])->middleware(['auth:api', 'admin']);
    Route::put('/{category}', [CategoriesController::class, 'update'])->middleware(['auth:api', 'admin']);
    Route::delete('/{category}', [CategoriesController::class, 'destroy'])->middleware(['auth:api', 'admin']);
});

// Shopping Cart routes
Route::middleware(['auth:api', 'verified.api'])->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/items', [CartController::class, 'addItem']);
    Route::put('/cart/items/{item}', [CartController::class, 'updateItem']);
    Route::delete('/cart/items/{item}', [CartController::class, 'removeItem']);
    Route::delete('/cart', [CartController::class, 'clearCart']);
    Route::get('/cart/total', [CartController::class, 'getTotal']);
});

// Order routes
Route::middleware(['auth:api', 'verified.api'])->group(function () {
    Route::post('/orders', [OrderController::class, 'createOrder']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::put('/orders/{order}/cancel', [OrderController::class, 'cancel']);
});

// Admin order routes
Route::middleware(['auth:api', 'admin'])->group(function () {
    Route::get('/admin/orders', [OrderController::class, 'adminIndex']);
    Route::get('/admin/orders/{order}', [OrderController::class, 'adminShow']);
    Route::put('/admin/orders/{order}/status', [OrderController::class, 'updateStatus']);
});

// Payment routes (webhook must be public for Stripe)
Route::post('/payments/webhook', [PaymentController::class, 'webhook']);

Route::middleware(['auth:api', 'verified.api'])->prefix('payments')->group(function () {
    Route::post('/create-checkout', [PaymentController::class, 'createCheckoutSession']);
    Route::post('/verify-session', [PaymentController::class, 'verifySession']);
});

// Public success/cancel pages
Route::get('/payments/success', function () {
    return 'Payment completed successfully! You can close this window.';
})->name('payment.success');

Route::get('/payments/cancel', function () {
    return 'Payment was cancelled. Please try again.';
})->name('payment.cancel');

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'emailVerify'])->middleware(['signed'])->name('verification.verify');
Route::post('/email/resend', [EmailVerificationController::class, 'resend'])->middleware('auth:api');
