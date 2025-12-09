<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\VNPayController;
use App\Http\Controllers\GeminiAIController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\Api\ReviewController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// 🔹 AUTH PUBLIC ROUTES
use Illuminate\Support\Facades\Mail;

Route::get('/test-mail', function () {
    Mail::raw('Test gửi mail thành công', function ($message) {
        $message->to('binhbcpk03952@gmail.com')
            ->subject('Test Mail Laravel');
    });

    return 'Đã gửi mail';
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',     [AuthController::class, 'login']);

// 🔹 CHAT AI PUBLIC ROUTE
Route::post('/chat-ai', [GeminiAIController::class, 'chat']);

// 🔹 PRODUCTS PUBLIC ROUTES
Route::get('/products',          [ProductController::class, 'index']);
Route::get('/products/{id}',     [ProductController::class, 'show']);

// 🔹 BANNERS PUBLIC ROUTES
Route::get('/banners', [BannerController::class, 'index']);


// 🔒 PROTECTED ROUTES (LOGIN REQUIRED)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout',     [AuthController::class, 'logout']);
    // Route Lấy thông tin người dùng
    Route::get('users', [AuthController::class, 'users']);
    Route::put('users/{id}', [AuthController::class, 'update']);
    Route::delete('users/{id}', [AuthController::class, 'destroy']);

    // route profile
    Route::get('user', [AuthController::class, 'user']);
    Route::post('profile', [AuthController::class, 'updateProfile']);
    Route::put('change-password', [AuthController::class, 'changePassword']);



    // Categories
    Route::post('/categories',       [CategoryController::class, 'store']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // Products
    Route::post('/products', [ProductController::class, 'store']);

    // Cart
    Route::get('/carts',          [CartController::class, 'index']);
    Route::post('/carts',         [CartController::class, 'store']);
    Route::put('/carts/{id}',     [CartController::class, 'updateQuantity']);
    Route::patch('/carts/{id}',     [CartController::class, 'updateVariant']);
    Route::delete('/carts/{id}', [CartController::class, 'destroy']);

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
    // Route cho Admin cập nhật trạng thái (cần middleware check admin nếu có)
    Route::post('/update-order-status/{id}', [OrderController::class, 'updateStatus']);

    // Route cho User tự huỷ đơn
    Route::post('/cancel-order/{id}', [OrderController::class, 'cancelOrder']);


    Route::get('/orders/latest', [OrderController::class, 'getLatestOrder']);
    Route::post('/vnpay_payment', [VNPayController::class, 'createPayment']);
    Route::get('/vnpay/return', [VNPayController::class, 'vnpayReturn']);

    Route::post('/logout', [AuthController::class, 'logout']);
    // Danh gia san pham
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/reviews/{id}', [ReviewController::class, 'index']);
});


Route::get('/categories',        [CategoryController::class, 'index']);
Route::get('banners', [BannerController::class, 'index']);
Route::get('orders_admin', [OrderController::class, 'getAllOrders']);

// Route cho Địa chỉ
Route::get('address/provinces', [AddressController::class, 'getProvinces']);
Route::get('address/districts/{provinceId}', [AddressController::class, 'getDistricts']);
Route::get('address/wards/{districtId}', [AddressController::class, 'getWards']);

Route::get('/auth/google', [SocialAuthController::class, 'googleRedirect']);
Route::get('/auth/google/callback', [SocialAuthController::class, 'googleCallback']);
