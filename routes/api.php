<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\ProfileController;
use App\Http\Controllers\api\RoleController;
use App\Http\Controllers\api\UserController;
use App\Http\Middleware\JwtCheckMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1'); // Sudah selesai

    Route::middleware(JwtCheckMiddleware::class)->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']); // Sudah selesai
        Route::post('/refresh', [AuthController::class, 'refresh']); // Sudah Selesai
    });
});


Route::middleware(JwtCheckMiddleware::class)->group(function () {
    // KHUSUS USER YANG LOGIN (AKUNNYA MEREKA SENDIRI)
    Route::post('/profile', [ProfileController::class, 'store']); // Sudah Selesai
    Route::get('/profile', [ProfileController::class, 'show']); // Sudah Selesai
    Route::delete('/profile', [ProfileController::class, 'destroy']); // Sudah Selesai

    // KHUSUS SUPER ADMIN ROUTES
    Route::middleware('role:super-admin')->group(function () {
        // CRUD Users
        Route::post('/admin/users', [UserController::class, 'store']); // Sudah Selesai
        Route::get('/admin/users', [UserController::class, 'index']);
        Route::get('/admin/users/{uuid}', [UserController::class, 'show']);
        Route::delete('/admin/users/{uuid}', [UserController::class, 'destroy']);
        Route::patch('/admin/users/{uuid}', [UserController::class, 'update']);

        // CRUD Roles
        Route::post('/roles', [RoleController::class, 'store']); // Sudah Selesai
        Route::get('/roles', [RoleController::class, 'index']); // Sudah Selesai
        Route::delete('/roles/{uuid}', [RoleController::class, 'destroy']); // Sudah Selesai
        Route::put('/roles/{uuid}', [RoleController::class, 'update']); // Sudah Selesai
    });
});
