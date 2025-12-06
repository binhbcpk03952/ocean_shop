<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\VNPayController;




use App\Models\Order;

// Route đăng ký

Route::post('/register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Route cần xác thực (Sử dụng middleware 'auth:sanctum')
Route::middleware('auth:sanctum')->group(function () {
    // Route Đăng xuất
    Route::post('logout', [AuthController::class, 'logout']);

    // Route Lấy thông tin người dùng
    Route::get('users', [AuthController::class, 'users']);
    Route::put('users/{id}', [AuthController::class, 'update']);
    Route::delete('users/{id}', [AuthController::class, 'destroy']);

    // route profile
    Route::get('user', [AuthController::class, 'user']);
    Route::post('profile', [AuthController::class, 'updateProfile']);
    Route::put('change-password', [AuthController::class, 'changePassword']);



    // route thêm danh mục
    // route danh sách danh mục
    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('categories', [CategoryController::class, 'store']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']);

    // Route cho Giỏ hàng
    Route::post('products', [ProductController::class, 'store']);
    // Route::post('cart', [CartController::class, 'store']);
    Route::post('carts', [CartController::class, 'store']);
    Route::get('carts', [CartController::class, 'index']);
    Route::put('carts/{id}', [CartController::class, 'updateQuantity']);

    Route::post('posts', [PostController::class, 'store']);
    Route::get('posts', [PostController::class, 'index']);
    Route::get('posts/{id}', [PostController::class, 'show']);
    Route::delete('posts/{id}', [PostController::class, 'destroy']);

    Route::post('banners', [BannerController::class, 'store']);
    Route::put('banners/{id}', [BannerController::class, 'update']);
    Route::delete('banners/{id}', [BannerController::class, 'destroy']);

    // route thêm địa chỉ
    Route::post('addresses', [AddressController::class, 'store']);
    Route::get('addresses', [AddressController::class, 'index']);

    // route thêm đơn hàng
    Route::get('orders', [OrderController::class, 'index']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('/orders/latest', [OrderController::class, 'getLatestOrder']);
});
Route::post('/vnpay_payment', [VNPayController::class, 'createPayment']);
Route::get('/vnpay/return', [VNPayController::class, 'vnpayReturn']);
Route::get('banners', [BannerController::class, 'index']);
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{id}', [ProductController::class, 'show']);

// Route cho Địa chỉ
Route::get('address/provinces', [AddressController::class, 'getProvinces']);
Route::get('address/districts/{provinceId}', [AddressController::class, 'getDistricts']);
Route::get('address/wards/{districtId}', [AddressController::class, 'getWards']);
