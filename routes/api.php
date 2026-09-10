<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/auth/register', [AuthController::class, 'register'])->name('api.v1.auth.register');
        Route::post('/auth/login', [AuthController::class, 'login'])->name('api.v1.auth.login');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
        Route::get('/auth/user', [AuthController::class, 'user'])->name('api.v1.auth.user');

        Route::get('/users', [UserController::class, 'index'])->name('api.v1.users.index');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('api.v1.users.show');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('api.v1.users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('api.v1.users.destroy');

        Route::get('/admin/users', [AdminController::class, 'users'])->name('api.v1.admin.users');
    });
});

Route::middleware('throttle:5,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
});
