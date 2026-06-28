<?php

use App\Http\Controllers\Web\DocsAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DocsAuthController::class, 'showLoginForm'])->name('docs.login');
Route::post('/', [DocsAuthController::class, 'login'])->name('docs.login.submit');
Route::post('/logout', [DocsAuthController::class, 'logout'])->name('docs.logout');
