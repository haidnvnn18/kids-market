<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\ProvinceController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\ShippingAddressController;
use App\Http\Controllers\Api\Seller\AuthController as SellerAuthController;
use App\Http\Controllers\Api\Seller\DashboardController as SellerDashboardController;
use App\Http\Controllers\Api\Seller\ShopController as SellerShopController;
use App\Http\Controllers\Api\Seller\ProductController as SellerProductController;
use App\Http\Controllers\Api\Seller\OrderController as SellerOrderController;
use App\Http\Controllers\Api\Seller\ReviewController as SellerReviewController;

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:api-register');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:api-login');
Route::post('/social-login', [AuthController::class, 'socialLogin'])->middleware('throttle:api-social-login');
Route::get('/check-username', [AuthController::class, 'checkUsername'])->middleware('throttle:api-check-username');

// Product routes
Route::get('/products/categories', [ProductController::class, 'categories']);
Route::get('/products/search', [ProductController::class, 'search']);
Route::get('/products/recommendations/home', [ProductController::class, 'getHomePageRecommendations'])->middleware('auth.token.optional');
Route::post('/products/{id}/track-view', [ProductController::class, 'trackView'])->middleware('auth.token');
Route::post('/products/{id}/like', [ProductController::class, 'toggleLike'])->middleware('auth.token');
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show'])->middleware('auth.token.optional');

// Review routes
Route::get('/products/{id}/reviews', [ReviewController::class, 'index']);
Route::post('/products/{id}/reviews', [ReviewController::class, 'store'])->middleware('auth.token');

// Cart routes
Route::get('/cart', [CartController::class, 'index'])->middleware('auth.token');
Route::post('/cart', [CartController::class, 'store'])->middleware('auth.token');

// Order routes
Route::middleware('auth.token')->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::patch('/orders/{id}/cancel', [OrderController::class, 'cancel']);
});

// Shop routes
Route::get('/shops/{id}', [ShopController::class, 'show']);
Route::get('/shops/{id}/products', [ShopController::class, 'getProducts']);

// Province and District routes
Route::get('/provinces', [ProvinceController::class, 'index']);
Route::get('/provinces/{id}', [ProvinceController::class, 'show']);
Route::get('/provinces/{provinceId}/districts', [DistrictController::class, 'index']);

// Shipping address routes
Route::middleware('auth.token')->group(function () {
    Route::get('/shipping-addresses', [ShippingAddressController::class, 'index']);
    Route::post('/shipping-addresses', [ShippingAddressController::class, 'store']);
    Route::put('/shipping-addresses/{id}', [ShippingAddressController::class, 'update']);
    Route::patch('/shipping-addresses/{id}/set-default', [ShippingAddressController::class, 'setDefault']);
    Route::delete('/shipping-addresses/{id}', [ShippingAddressController::class, 'destroy']);
});

// Image upload route
Route::post('/upload-image', [App\Http\Controllers\Api\ImageUploadController::class, 'upload'])->middleware('auth.token');

// -------------------------------------------------------------------------
// Seller API routes
// -------------------------------------------------------------------------
Route::prefix('seller')->group(function () {

    // Public: Auth
    Route::post('/auth/register', [SellerAuthController::class, 'register'])->middleware('throttle:api-register');
    Route::post('/auth/login', [SellerAuthController::class, 'login'])->middleware('throttle:api-login');

    // Protected: requires valid seller JWT
    Route::middleware('auth.token.seller')->group(function () {
        Route::post('/auth/logout', [SellerAuthController::class, 'logout']);

        // Dashboard
        Route::get('/dashboard', [SellerDashboardController::class, 'index']);

        // Shop
        Route::get('/shop', [SellerShopController::class, 'show']);
        Route::put('/shop', [SellerShopController::class, 'update']);
        Route::delete('/shop/avatar', [SellerShopController::class, 'deleteAvatar']);
        Route::delete('/shop/banner', [SellerShopController::class, 'deleteBanner']);

        // Products
        Route::get('/products', [SellerProductController::class, 'index']);
        Route::post('/products', [SellerProductController::class, 'store']);
        Route::get('/products/{id}', [SellerProductController::class, 'show']);
        Route::put('/products/{id}', [SellerProductController::class, 'update']);
        Route::delete('/products/{id}', [SellerProductController::class, 'destroy']);
        Route::delete('/products/images/{imageId}', [SellerProductController::class, 'deleteImage']);

        // Orders
        Route::get('/orders', [SellerOrderController::class, 'index']);
        Route::get('/orders/{id}', [SellerOrderController::class, 'show']);
        Route::patch('/orders/{id}/payment-status', [SellerOrderController::class, 'updatePaymentStatus']);
        Route::patch('/orders/{id}/shipment-status', [SellerOrderController::class, 'updateShipmentStatus']);

        // Reviews
        Route::get('/reviews', [SellerReviewController::class, 'index']);
        Route::post('/reviews/{id}/reply', [SellerReviewController::class, 'reply']);

        // Image upload
        Route::post('/upload-image', [App\Http\Controllers\Api\ImageUploadController::class, 'upload']);
    });
});

