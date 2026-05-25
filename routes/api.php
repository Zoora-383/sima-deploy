<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\RoleController;
use App\Http\Controllers\api\UserController;
use App\Http\Middleware\JwtCheckMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware(JwtCheckMiddleware::class)->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });
});


Route::middleware(JwtCheckMiddleware::class)->group(function () {
    // CRUD Users
    Route::delete('/users', [UserController::class, 'destroy']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users', [UserController::class, 'show']);

    // CRUD Roles
    Route::post('/roles', [RoleController::class, 'store']);
});
