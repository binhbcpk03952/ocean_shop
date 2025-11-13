<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;

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
});
Route::post('categories', [CategoryController::class, 'store']);
