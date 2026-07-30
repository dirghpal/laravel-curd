<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\PostController as PostApiController;
use App\Http\Controllers\Api\ProductController as ProductApiController; 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;

Route::get('email/verify', function () {
    return api_error('Your email address is not verified.', 403);
})->name('verification.notice');

Route::prefix('v1')->name('api.')->group(function () {
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->name('register');
        Route::post('login', [AuthController::class, 'login'])->name('login');
        Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
        Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');
    });

    Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware('signed')
        ->name('verification.verify');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('user', [AuthController::class, 'me'])->name('me');
        Route::get('tokens', [AuthController::class, 'tokens'])->name('tokens.index');
        Route::delete('tokens/{token}', [AuthController::class, 'revokeToken'])->name('tokens.destroy');
        Route::post('email/verification-notification', [AuthController::class, 'sendVerificationEmail'])
            ->middleware('throttle:6,1')
            ->name('verification.send');
                                                         
        Route::middleware('verified')->group(function () {
        Route::get('posts/{post}/comments', [CommentController::class, 'index'])->name('posts.comments.index');
        Route::post('posts/{post}/comments', [CommentController::class, 'store'])->name('posts.comments.store');
        Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
        Route::post('posts/{post}/likes', [LikeController::class, 'store'])->name('posts.likes.store');
        Route::delete('posts/{post}/likes', [LikeController::class, 'destroy'])->name('posts.likes.destroy');
        Route::apiResource('posts', PostApiController::class)->except(['store', 'update', 'destroy']);
        Route::apiResource('products', ProductApiController::class)->except(['store', 'update', 'destroy']);
        Route::apiResource('categories', CategoryController::class)->except(['store', 'update', 'destroy']);

        Route::middleware('role:admin')->group(function () {
            Route::apiResource('posts', PostApiController::class)->only(['store', 'update', 'destroy']);
            Route::apiResource('products', ProductApiController::class)->only(['store', 'update', 'destroy']);
            Route::apiResource('categories', CategoryController::class)->only(['store', 'update', 'destroy']);
        });
        });
    });
});

Route::fallback(function () {
    return api_error('API endpoint not found.', 404);
});
