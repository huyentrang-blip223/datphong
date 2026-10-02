<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\Admin\HomestayController;
use App\Http\Controllers\Host\RoomController;
use App\Http\Controllers\Product\LocalProductController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::get('/tim-kiem', [SearchController::class, 'index'])->name('search.index');
Route::get('/tim-kiem/ket-qua', [SearchController::class, 'results'])->name('search.results');
Route::get('/san-pham-dia-phuong', [LocalProductController::class, 'index'])->name('products.index');
Route::get('/san-pham-dia-phuong/{product}', [LocalProductController::class, 'show'])->name('products.show');
Route::get('/trai-nghiem', [ExperienceController::class, 'index'])->name('experiences.index');
Route::get('/trai-nghiem/{experience}', [ExperienceController::class, 'show'])->name('experiences.show');

Route::middleware('guest')->group(function () {
    Route::get('/dang-nhap', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'login'])->name('login.submit');
});
Route::post('/dang-xuat', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::post('/bookings', [BookingController::class, 'store'])->middleware(['auth','role:guest'])->name('bookings.store');
Route::post('/trai-nghiem/{experience}/dat-cho', [ExperienceController::class, 'book'])->middleware(['auth','role:guest'])->name('experiences.book');

Route::middleware(['auth','role:admin'])->prefix('quan-tri')->name('admin.')->group(function () {
    Route::get('/bang-dieu-khien', [DashboardController::class, 'admin'])->name('dashboard');
    Route::resource('homestays', HomestayController::class)->except(['show']);
});

Route::middleware(['auth','role:host'])->prefix('chu-homestay')->name('host.')->group(function () {
    Route::get('/bang-dieu-khien', [DashboardController::class, 'host'])->name('dashboard');
    Route::get('/homestays/{homestay}/edit', [DashboardController::class, 'editOwnedHomestay'])->name('homestays.edit');
    Route::resource('rooms', RoomController::class)->except(['show', 'destroy']);
});
