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
use App\Http\Controllers\GeminiAIController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// 🔹 AUTH PUBLIC ROUTES
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

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user',    [AuthController::class, 'user']);

    // Users
    Route::get('/users',         [AuthController::class, 'users']);
    Route::put('/users/{id}',    [AuthController::class, 'update']);
    Route::delete('/users/{id}', [AuthController::class, 'destroy']);

    // Categories
    Route::get('/categories',        [CategoryController::class, 'index']);
    Route::post('/categories',       [CategoryController::class, 'store']);
    Route::delete('/categories/{id}',[CategoryController::class, 'destroy']);

    // Products
    Route::post('/products', [ProductController::class, 'store']);

    // Cart
    Route::get('/carts',          [CartController::class, 'index']);
    Route::post('/carts',         [CartController::class, 'store']);
    Route::put('/carts/{id}',     [CartController::class, 'updateQuantity']);

    // Posts
    Route::get('/posts',        [PostController::class, 'index']);
    Route::get('/posts/{id}',   [PostController::class, 'show']);
    Route::post('/posts',       [PostController::class, 'store']);
    Route::delete('/posts/{id}',[PostController::class, 'destroy']);

    // Banners
    Route::post('/banners',        [BannerController::class, 'store']);
    Route::put('/banners/{id}',    [BannerController::class, 'update']);
    Route::delete('/banners/{id}', [BannerController::class, 'destroy']);
});
