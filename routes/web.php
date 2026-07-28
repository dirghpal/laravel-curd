<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProductController;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/dashboard', 'dashboard');

Route::get('/login', function () {
    return response()->json(['message' => 'Please use API authentication'], 401);
})->name('login');

Route::resource('posts', PostController::class);
Route::resource('products', ProductController::class);
