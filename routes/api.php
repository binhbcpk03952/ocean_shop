<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;


// Route đăng ký

Route::post('/register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Route cần xác thực (Sử dụng middleware 'auth:sanctum')
Route::middleware('auth:sanctum')->group(function () {
    // Route Đăng xuất
    Route::post('logout', [AuthController::class, 'logout']);

    // Route Lấy thông tin người dùng
    Route::get('user', [AuthController::class, 'user']);


    // route thêm danh mục
    // route danh sách danh mục
    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('categories', [CategoryController::class, 'store']);

    // Route cho Giỏ hàng
    Route::post('cart', [\App\Http\Controllers\Api\CartController::class, 'store']);
    Route::post('products', [ProductController::class, 'store']);
    Route::post('carts', [CartController::class, 'store']);
    Route::get('carts', [CartController::class, 'index']);
});
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{id}', [ProductController::class, 'show']);