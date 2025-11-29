<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\BannerController;



// Route đăng ký

Route::post('/register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Route cần xác thực (Sử dụng middleware 'auth:sanctum')
Route::middleware('auth:sanctum')->group(function () {
    // Route Đăng xuất
    Route::post('logout', [AuthController::class, 'logout']);

    // Route Lấy thông tin người dùng
    Route::get('user', [AuthController::class, 'user']);
    Route::get('users', [AuthController::class, 'users']);
    Route::put('users/{id}', [AuthController::class, 'update']);
    Route::delete('users/{id}', [AuthController::class, 'destroy']);


    // route thêm danh mục
    // route danh sách danh mục
    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('categories', [CategoryController::class, 'store']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']);

    // Route cho Giỏ hàng
    Route::post('cart', [\App\Http\Controllers\Api\CartController::class, 'store']);
    Route::post('products', [ProductController::class, 'store']);
    Route::post('carts', [CartController::class, 'store']);
    Route::get('carts', [CartController::class, 'index']);
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
});
Route::get('banners', [BannerController::class, 'index']);
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{id}', [ProductController::class, 'show']);
Route::get('products/{id}', [ProductController::class, 'show']);
