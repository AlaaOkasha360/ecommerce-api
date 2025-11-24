<?php

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

Route::group(['middleware'=>'auth:api', 'prefix'=>'users'], function(){
    Route::get('/profile', [UserController::class, 'show_profile']);
    Route::put('/profile', [UserController::class, 'update_profile']);
    Route::get('/addresses', [UserController::class, 'show_addresses']);
    Route::post('/addresses', [UserController::class, 'create_address']);
    Route::put('/addresses/{address}', [UserController::class, 'update_address']);
    Route::delete('/addresses/{address}', [UserController::class, 'delete_address']);
    Route::get('/orders', [UserController::class, 'index_orders']);
    Route::get('/orders/{order}', [UserController::class, 'show_order']);
});

Route::group(['middleware'=>['auth:api', 'admin'], 'prefix'=>'admin'], function(){
    Route::get('/users', []);
});
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/products/category/{id}', [ProductController::class, 'product_category']);
Route::get('/categories', [CategoriesController::class, 'index']);
Route::get('/categories/{id}/products', [CategoriesController::class, 'show']);
