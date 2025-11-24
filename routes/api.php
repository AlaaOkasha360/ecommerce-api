<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware(['auth:api'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::group(['middleware' => 'auth:api', 'prefix' => 'users'], function () {
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
    Route::post('/{product}/reviews', [ProductController::class, 'store_review'])->middleware('auth:api');

});

// Category routes
Route::prefix('categories')->group(function () {
    Route::get('', [CategoriesController::class, 'index']);
    Route::get('/{category}', [CategoriesController::class, 'show']);
    Route::post('', [CategoriesController::class, 'store'])->middleware(['auth:api', 'admin']);
    Route::put('/{category}', [CategoriesController::class, 'update'])->middleware(['auth:api', 'admin']);
    Route::delete('/{category}', [CategoriesController::class, 'destroy'])->middleware(['auth:api', 'admin']);
});
