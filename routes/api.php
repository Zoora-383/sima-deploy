<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\ItemCategoryController;
use App\Http\Controllers\api\ProfileController;
use App\Http\Controllers\api\RoleController;
use App\Http\Controllers\api\ItemController;
use App\Http\Controllers\api\MaintenanceController;
use App\Http\Controllers\api\UserController;
use App\Http\Middleware\JwtCheckMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

        Route::middleware(JwtCheckMiddleware::class)->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
        });
    });


    Route::middleware(JwtCheckMiddleware::class)->group(function () {
        // KHUSUS USER YANG LOGIN (AKUNNYA MEREKA SENDIRI)
        Route::post('/profile', [ProfileController::class, 'store']);
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::delete('/profile', [ProfileController::class, 'destroy']);

        // KHUSUS SUPER ADMIN ROUTES
        Route::middleware('role:super-admin')->group(function () {
            // CRUD Users
            Route::post('/admin/users', [UserController::class, 'store']);
            Route::get('/admin/users', [UserController::class, 'index']);
            Route::get('/admin/users/{uuid}', [UserController::class, 'show']);
            Route::delete('/admin/users/{uuid}', [UserController::class, 'destroy']);
            Route::patch('/admin/users/{uuid}', [UserController::class, 'update']);
            Route::patch('/admin/users/{uuid}/status', [UserController::class, 'updateStatus']);

            // CRUD Roles
            Route::post('/roles', [RoleController::class, 'store']);
            Route::get('/roles', [RoleController::class, 'index']);
            Route::delete('/roles/{uuid}', [RoleController::class, 'destroy']);
            Route::put('/roles/{uuid}', [RoleController::class, 'update']);
        });

        // KHUSUS ADMIN ROUTES
        Route::middleware('role:admin')->group(function () {
            // CRUD Items Category
            Route::post('/item-category', [ItemCategoryController::class, 'store']);
            Route::get('/item-category', [ItemCategoryController::class, 'index']);
            Route::delete('/item-category/{uuid}', [ItemCategoryController::class, 'destroy']);
            Route::put('/item-category/{uuid}', [ItemCategoryController::class, 'update']);

            // CRUD Items
            Route::post('/items', [ItemController::class, 'store']);
            Route::get('/items', [ItemController::class, 'index']);
            Route::get('/items/{uuid}', [ItemController::class, 'show']);
            Route::delete('/items/{uuid}', [ItemController::class, 'destroy']);
            Route::put('/items/{uuid}', [ItemController::class, 'update']);
            Route::patch('/items/{uuid}/status', [ItemController::class, 'updateStatus']);

            // CRUD Maintenance
            Route::post('/maintenance', [MaintenanceController::class, 'store']);
            Route::get('/maintenance', [MaintenanceController::class, 'index']);
            Route::get('/maintenance/{uuid}', [MaintenanceController::class, 'show']);
            Route::delete('/maintenance/{uuid}', [MaintenanceController::class, 'destroy']);
            Route::patch('/maintenance/{uuid}/status', [MaintenanceController::class, 'updateStatus']);
        });
    });
});
