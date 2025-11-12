<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Route cần xác thực (Sử dụng middleware 'auth:sanctum')
Route::middleware('auth:sanctum')->group(function () {
    // Route Đăng xuất
    Route::post('logout', [AuthController::class, 'logout']);

    // Route Lấy thông tin người dùng
    Route::get('user', [AuthController::class, 'user']);

    // Các route API khác cần bảo vệ...
});
