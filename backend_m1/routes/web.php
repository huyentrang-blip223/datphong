<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\HomestayController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/dang-nhap', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'login'])->name('login.submit');
});
Route::post('/dang-xuat', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth','role:admin'])->prefix('quan-tri')->name('admin.')->group(function () {
    Route::get('/bang-dieu-khien', [DashboardController::class, 'admin'])->name('dashboard');
    Route::resource('homestays', HomestayController::class)->except(['show']);
});

Route::middleware(['auth','role:host'])->prefix('chu-homestay')->name('host.')->group(function () {
    Route::get('/bang-dieu-khien', [DashboardController::class, 'host'])->name('dashboard');
    Route::get('/homestays/{homestay}/edit', [DashboardController::class, 'editOwnedHomestay'])->name('homestays.edit');
});
