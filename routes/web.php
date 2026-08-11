<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Site\DashboardController as SiteDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest:staff')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth:staff')
    ->name('logout');

Route::middleware(['auth:staff', 'super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
});

Route::middleware('auth:staff')->prefix('site')->name('site.')->group(function () {
    Route::get('/dashboard', [SiteDashboardController::class, 'index'])->name('dashboard');
});
