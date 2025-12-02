<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\GeminiAIController;

//  AUTH
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


//  ROUTES CẦN XÁC THỰC
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('logout', [AuthController::class, 'logout']);

    // User
    Route::get('user', [AuthController::class, 'user']);
    Route::get('users', [AuthController::class, 'users']);
    Route::put('users/{id}', [AuthController::class, 'update']);
    Route::delete('users/{id}', [AuthController::class, 'destroy']);

    // Categories
    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('categories', [CategoryController::class, 'store']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']);

    // Products
    Route::post('products', [ProductController::class, 'store']);

    // Cart
    Route::post('carts', [CartController::class, 'store']);
    Route::get('carts', [CartController::class, 'index']);
    Route::put('carts/{id}', [CartController::class, 'updateQuantity']);

    // Posts
    Route::post('posts', [PostController::class, 'store']);
    Route::get('posts', [PostController::class, 'index']);
    Route::get('posts/{id}', [PostController::class, 'show']);
    Route::delete('posts/{id}', [PostController::class, 'destroy']);

    // Banner
    Route::post('banners', [BannerController::class, 'store']);
    Route::put('banners/{id}', [BannerController::class, 'update']);
    Route::delete('banners/{id}', [BannerController::class, 'destroy']);

    // REVIEW
    Route::post('reviews', [ReviewController::class, 'store']);
    Route::put('reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('reviews/{id}', [ReviewController::class, 'destroy']);
});

//  PUBLIC ROUTES

// Banner
Route::get('banners', [BannerController::class, 'index']);

// Products
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{id}', [ProductController::class, 'show']);

// Review theo sản phẩm
Route::get('products/{id}/reviews', [ReviewController::class, 'getByProduct']);

//  CHAT AI (PUBLIC)
Route::post('/chat-ai', [GeminiAIController::class, 'chat']);
