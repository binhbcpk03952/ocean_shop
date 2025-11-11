<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;


// Tất cả route frontend sẽ render Vue app
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
