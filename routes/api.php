<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\ItemCategoryController;
use App\Http\Controllers\api\ProfileController;
use App\Http\Controllers\api\RoleController;
use App\Http\Controllers\api\ItemController;
use App\Http\Controllers\api\MaintenanceController;
use App\Http\Controllers\api\NotificationController;
use App\Http\Controllers\api\SpkController;
use App\Http\Controllers\api\UserController;
use App\Http\Middleware\JwtCheckMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

        // Auth routes — JWT required, force.password.change diizinkan agar user bisa change/logout/refresh
        Route::middleware([JwtCheckMiddleware::class])->group(function () {
            Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('throttle:change-password');
            Route::post('/logout', [AuthController::class, 'logout'])->middleware('throttle:30,1');
            Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('throttle:10,1');
        });
    });

    // Protected routes — JWT + Force Password Change check
    Route::middleware([JwtCheckMiddleware::class, 'force.password.change'])->group(function () {
        // --- PROFILE & ACCOUNT ---
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::match(['put', 'patch'], '/profile', [ProfileController::class, 'update']);
        Route::delete('/profile', [ProfileController::class, 'destroy'])->middleware('throttle:change-password');
        Route::put('/profile/reset-password', [ProfileController::class, 'updateMyPassword'])->middleware('throttle:change-password');

        // --- NOTIFICATIONS ---
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/notifications', [NotificationController::class, 'deleteAll']);
        Route::patch('/notifications/{uuid}/read', [NotificationController::class, 'markAsRead']);
        Route::delete('/notifications/{uuid}', [NotificationController::class, 'destroy']);

        // --- USERS (SUPER ADMIN) ---
        Route::middleware('role:super-admin')->group(function () {
            Route::apiResource('admin/users', UserController::class)->parameters(['users' => 'uuid']);
            Route::patch('/admin/users/{uuid}/status', [UserController::class, 'updateStatus']);
            Route::post('/reset-password/{uuid}', [AuthController::class, 'resetPassword'])->middleware('throttle:change-password');
        });

        // --- ROLES (SUPER ADMIN) ---
        Route::middleware('role:super-admin')->group(function () {
            Route::apiResource('roles', RoleController::class)->parameters(['roles' => 'uuid'])->except(['show']);
        });

        // --- ITEM CATEGORIES ---
        Route::get('/item-category', [ItemCategoryController::class, 'index']);
        Route::middleware('role:admin')->group(function () {
            Route::post('/item-category', [ItemCategoryController::class, 'store']);
            Route::put('/item-category/{uuid}', [ItemCategoryController::class, 'update']);
            Route::delete('/item-category/{uuid}', [ItemCategoryController::class, 'destroy']);
        });

        // --- ITEMS ---
        Route::get('/items', [ItemController::class, 'index']);
        Route::get('/items/{uuid}', [ItemController::class, 'show']);
        Route::middleware('role:admin')->group(function () {
            Route::post('/items', [ItemController::class, 'store']);
            Route::put('/items/{uuid}', [ItemController::class, 'update']);
            Route::delete('/items/{uuid}', [ItemController::class, 'destroy']);
        });
        // Item Approval Workflow
        Route::middleware('role:admin,kasi,kel_pust')->patch('/items/{uuid}/status', [ItemController::class, 'updateStatus']);

        // --- MAINTENANCE REQUESTS ---
        Route::get('/maintenance', [MaintenanceController::class, 'index']);
        Route::middleware('role:admin')->group(function () {
            Route::get('/maintenance/rekaps', [MaintenanceController::class, 'indexRekap']);
            Route::get('/maintenance/rekaps/{rekap_uuid}', [MaintenanceController::class, 'showRekap']);
            Route::delete('/maintenance/rekaps/{rekap_uuid}', [MaintenanceController::class, 'destroyRekap']);
        });

        Route::get('/maintenance/{uuid}', [MaintenanceController::class, 'show']);
        Route::middleware('role:admin')->group(function () {
            Route::post('/maintenance', [MaintenanceController::class, 'store']);
            Route::put('/maintenance/{uuid}', [MaintenanceController::class, 'update']);
            Route::delete('/maintenance/{uuid}', [MaintenanceController::class, 'destroy']);
        });
        // Maintenance Approval Workflow
        Route::middleware('role:admin,kasi,kel_pust')->patch('/maintenance/{uuid}/status', [MaintenanceController::class, 'updateStatus']);
        Route::middleware('role:admin')->group(function () {
            Route::patch('/maintenance/{uuid}/rekap', [MaintenanceController::class, 'updateRekap']);
        });

        Route::middleware('role:admin,kel_pust')->group(function () {
            //  --- SPK (SURAT PERINTAH KERJA) ---
            Route::get('/spk', [SpkController::class, 'index']);
            Route::get('/spk/{uuid}', [SpkController::class, 'show']);
            Route::get('/spk/{uuid}/pdf', [SpkController::class, 'exportPdf'])->middleware('throttle:5,1');
            Route::middleware('role:kel_pust')->group(function () {
                Route::post('/spk', [SpkController::class, 'store']);
                Route::patch('/spk/{uuid}', [SpkController::class, 'update']);
                Route::delete('/spk/{uuid}', [SpkController::class, 'destroy']);
            });
        });
    });
});
