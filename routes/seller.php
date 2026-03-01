<?php

use App\Http\Controllers\Seller\AuthController;
use App\Http\Controllers\Seller\DashboardController;
use App\Http\Controllers\Seller\OrderController;
use App\Http\Controllers\Seller\PasswordResetController;
use App\Http\Controllers\Seller\ProductController;
use App\Http\Controllers\Seller\ShopController;
use Illuminate\Support\Facades\Route;

// Guest routes (not authenticated)
Route::middleware('guest')->prefix('seller')->name('seller.')->group(function () {
    // Login
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:seller-login');

    // Register
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:seller-register');

    // Password Reset
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPasswordForm'])
        ->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])
        ->middleware('throttle:seller-password-reset')
        ->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetPasswordForm'])
        ->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])
        ->middleware('throttle:seller-password-reset')
        ->name('password.update');
});

// Protected routes (authenticated sellers only)
Route::middleware('seller')->prefix('seller')->name('seller.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Shop Management
    Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
    Route::get('/shop/edit', [ShopController::class, 'edit'])->name('shop.edit');
    Route::put('/shop', [ShopController::class, 'update'])->name('shop.update');
    Route::delete('/shop/avatar', [ShopController::class, 'deleteAvatar'])->name('shop.delete-avatar');
    Route::delete('/shop/banner', [ShopController::class, 'deleteBanner'])->name('shop.delete-banner');

    // Product Management
    Route::resource('products', ProductController::class)->except(['show']);
    Route::delete('/products/images/{image}', [ProductController::class, 'deleteImage'])->name('products.delete-image');

    // Order Management
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // TODO: Add other seller routes here (reviews)
});
