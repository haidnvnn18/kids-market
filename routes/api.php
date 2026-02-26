<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\ProvinceController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\ShippingAddressController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/social-login', [AuthController::class, 'socialLogin']);
Route::get('/check-username', [AuthController::class, 'checkUsername']);

// Product routes
Route::get('/products/search', [ProductController::class, 'search']);
Route::get('/products/recommendations/home', [ProductController::class, 'getHomePageRecommendations'])->middleware('auth.token.optional');
Route::post('/products/{id}/track-view', [ProductController::class, 'trackView'])->middleware('auth.token');
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// Review routes
Route::get('/products/{id}/reviews', [ReviewController::class, 'index']);
Route::post('/products/{id}/reviews', [ReviewController::class, 'store'])->middleware('auth.token');

// Cart routes
Route::get('/cart', [CartController::class, 'index'])->middleware('auth.token');
Route::post('/cart', [CartController::class, 'store'])->middleware('auth.token');

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
