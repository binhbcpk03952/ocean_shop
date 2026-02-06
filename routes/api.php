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
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\MomoController;
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
Route::get('/products/{id}/related', [ProductController::class, 'relatedProducts']);
Route::get('/products_new',      [ProductController::class, 'getNewProduct']);

// 🔹 BANNERS PUBLIC ROUTES
Route::get('/banners', [BannerController::class, 'index']);
Route::get('/brands', [BrandController::class, 'index']);

Route::get('posts', [PostController::class, 'index']);
Route::get('posts/{slug}', [PostController::class, 'show']);

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

    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Categories
    Route::post('/categories',       [CategoryController::class, 'store']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // Brands
    Route::post('/brands',       [BrandController::class, 'store']);
    Route::put('/brands/{id}',   [BrandController::class, 'update']);
    Route::delete('/brands/{id}', [BrandController::class, 'destroy']);


    // Products
    Route::post('/products', [ProductController::class, 'store']);
    // Route::get('/admin/products', [ProductController::class, 'getAllProduct']);
    Route::put('/products/{id}', [ProductController::class, 'update']);

    // Cart
    Route::get('/carts',          [CartController::class, 'index']);
    Route::post('/carts',         [CartController::class, 'store']);
    Route::put('/carts/{id}',     [CartController::class, 'updateQuantity']);
    Route::patch('/carts/{id}',     [CartController::class, 'updateVariant']);
    Route::delete('/carts/{id}', [CartController::class, 'destroy']);

    Route::post('posts', [PostController::class, 'store']);
    Route::delete('posts/{id}', [PostController::class, 'destroy']);

    Route::post('banners', [BannerController::class, 'store']);
    Route::put('banners/{id}', [BannerController::class, 'update']);
    Route::delete('banners/{id}', [BannerController::class, 'destroy']);

    // route thêm địa chỉ
    Route::post('addresses', [AddressController::class, 'store']);
    Route::get('addresses', [AddressController::class, 'index']);
    Route::delete('addresses/{id}', [AddressController::class, 'destroy']);
    Route::put('addresses/{id}', [AddressController::class, 'update']);
    Route::put('addresses/default/{id}', [AddressController::class, 'setDefault']);


    // route thêm đơn hàng
    Route::get('orders', [OrderController::class, 'index']);
    Route::post('orders', [OrderController::class, 'store']);
    // Route cho Admin cập nhật trạng thái (cần middleware check admin nếu có)
    Route::post('/update-order-status/{id}', [OrderController::class, 'updateStatus']);
    // huy don co ly do
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);


    // Route cho User tự huỷ đơn
    Route::post('/cancel-order/{id}', [OrderController::class, 'cancelOrder']);


    Route::get('/orders/latest', [OrderController::class, 'getLatestOrder']);
    Route::post('/vnpay_payment', [VNPayController::class, 'createPayment']);
    Route::get('/vnpay/return', [VNPayController::class, 'vnpayReturn']);

    Route::post('/momo_payment', [MomoController::class, 'createPayment']);
    Route::get('/momo/return', [MomoController::class, 'momoReturn']);

    Route::post('/logout', [AuthController::class, 'logout']);
    // Danh gia san pham

    // Mã giảm giá
    Route::get('/voucher', [PromotionController::class, 'index']);
    Route::post('/voucher', [PromotionController::class, 'store']);
    Route::patch('/voucher/{id}', [PromotionController::class, 'update']);

    //
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::post('/reviews', [ReviewController::class, 'store']);

    // thong ke
    Route::get('/reports', [ReportController::class, 'index']);
}); //// ====== ////

Route::get('/products/{id}/reviews', [ReviewController::class, 'getByProduct']);

// );

Route::get('/categories',        [CategoryController::class, 'index']);
Route::get('banners', [BannerController::class, 'index']);
Route::get('orders_admin', [OrderController::class, 'getAllOrders']);

// Route cho Địa chỉ
Route::get('address/provinces', [AddressController::class, 'getProvinces']);
Route::get('address/districts/{provinceId}', [AddressController::class, 'getDistricts']);
Route::get('address/wards/{districtId}', [AddressController::class, 'getWards']);

Route::get('/auth/google', [SocialAuthController::class, 'googleRedirect']);
Route::get('/auth/google/callback', [SocialAuthController::class, 'googleCallback']);
