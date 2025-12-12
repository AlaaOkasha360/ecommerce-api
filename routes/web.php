<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Frontend routes for the e-commerce application
|
*/

// Home
Route::get('/', function () {
    return view('home');
});

// Products
Route::get('/products', function () {
    return view('products.index');
});

Route::get('/products/{id}', function ($id) {
    return view('products.show', ['id' => $id]);
});

// Cart
Route::get('/cart', function () {
    return view('cart.index');
});

// Authentication
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

// User Account (protected pages - auth handled by frontend)
Route::get('/profile', function () {
    return view('profile.index');
});

Route::get('/orders', function () {
    return view('orders.index');
});

Route::get('/addresses', function () {
    return view('addresses.index');
});

// Payment callbacks
Route::get('/checkout/success', function () {
    return view('checkout.success');
})->name('checkout.success');

Route::get('/checkout/cancel', function () {
    return view('checkout.cancel');
})->name('checkout.cancel');

// Admin Routes
Route::prefix('admin')->group(function () {
    // Admin Dashboard
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Admin Products
    Route::get('/products', function () {
        return view('admin.products.index');
    })->name('admin.products');

    // Admin Categories
    Route::get('/categories', function () {
        return view('admin.categories.index');
    })->name('admin.categories');

    // Admin Orders
    Route::get('/orders', function () {
        return view('admin.orders.index');
    })->name('admin.orders');

    // Admin Users
    Route::get('/users', function () {
        return view('admin.users.index');
    })->name('admin.users');
});
