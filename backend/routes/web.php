<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\ExperienceBookingPaymentController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Admin\HomestayController;
use App\Http\Controllers\Admin\PaymentOverviewController as AdminPaymentOverviewController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\RoomController as AdminRoomController;
use App\Http\Controllers\Host\AvailabilityController;
use App\Http\Controllers\Host\BookingController as HostBookingController;
use App\Http\Controllers\Host\ExperienceController as HostExperienceController;
use App\Http\Controllers\Host\SeasonalPriceController;
use App\Http\Controllers\Host\RoomController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Product\LocalProductController;
use App\Http\Controllers\ProductOrderController;
use App\Http\Controllers\ProductOrderPaymentController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Seller\ProductController as SellerProductController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoomDetailController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/phong/{room}', [RoomDetailController::class, 'show'])->name('rooms.show');
Route::get('/healthz', HealthController::class)->name('healthz');
Route::get('/tim-kiem', [SearchController::class, 'index'])->name('search.index');
Route::get('/tim-kiem/ket-qua', [SearchController::class, 'results'])->name('search.results');
Route::get('/san-pham-dia-phuong', [LocalProductController::class, 'index'])->name('products.index');
Route::get('/san-pham-dia-phuong/{product}', [LocalProductController::class, 'show'])->name('products.show');
Route::get('/trai-nghiem', [ExperienceController::class, 'index'])->name('experiences.index');
Route::get('/trai-nghiem/{experience}', [ExperienceController::class, 'show'])->name('experiences.show');

Route::middleware('guest')->group(function () {
    Route::get('/dang-nhap', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.submit');
    Route::get('/dang-ky', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/dang-ky', [AuthController::class, 'register'])->middleware('throttle:login')->name('register.submit');
});
Route::post('/dang-xuat', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::post('/bookings', [BookingController::class, 'store'])->middleware(['auth','role:guest'])->name('bookings.store');
Route::post('/trai-nghiem/{experience}/dat-cho', [ExperienceController::class, 'book'])->middleware(['auth','role:guest'])->name('experiences.book');
Route::middleware(['auth'])->prefix('tai-khoan')->name('account.')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::get('/loyalty', [LoyaltyController::class, 'index'])->name('loyalty.index');
});
Route::middleware(['auth','role:guest'])->prefix('tai-khoan')->name('guest.')->group(function () {
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::get('/bookings/{booking}/payment', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('/bookings/{booking}/payments/vnpay', [PaymentController::class, 'createVnPay'])->name('payments.vnpay');
    Route::post('/bookings/{booking}/payments/mock', [PaymentController::class, 'store'])->name('payments.mock');
    Route::post('/bookings/{booking}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/orders', [ProductOrderController::class, 'index'])->name('orders.index');
    Route::post('/orders', [ProductOrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}/payment', [ProductOrderPaymentController::class, 'show'])->name('order-payments.show');
    Route::post('/orders/{order}/payments/mock', [ProductOrderPaymentController::class, 'store'])->name('order-payments.mock');
    Route::get('/experience-bookings/{experienceBooking}/payment', [ExperienceBookingPaymentController::class, 'show'])->name('experience-payments.show');
    Route::post('/experience-bookings/{experienceBooking}/payments/mock', [ExperienceBookingPaymentController::class, 'store'])->name('experience-payments.mock');
});

Route::middleware(['auth','role:admin'])->prefix('quan-tri')->name('admin.')->group(function () {
    Route::get('/bang-dieu-khien', [DashboardController::class, 'admin'])->name('dashboard');
    Route::resource('homestays', HomestayController::class)->except(['show']);
    Route::resource('rooms', AdminRoomController::class)->except(['show']);
    Route::resource('products', AdminProductController::class)->except(['show']);
    Route::get('/payments', [AdminPaymentOverviewController::class, 'index'])->name('payments.index');
    Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');
});

Route::middleware(['auth','role:host'])->prefix('chu-homestay')->name('host.')->group(function () {
    Route::get('/bang-dieu-khien', [DashboardController::class, 'host'])->name('dashboard');
    Route::get('/homestays/{homestay}/edit', [DashboardController::class, 'editOwnedHomestay'])->name('homestays.edit');
    Route::resource('rooms', RoomController::class)->except(['show', 'destroy']);
    Route::get('/bookings', [HostBookingController::class, 'index'])->name('bookings.index');
    Route::resource('experiences', HostExperienceController::class)->except(['show']);
    Route::get('/availability', [AvailabilityController::class, 'index'])->name('availability.index');
    Route::post('/rooms/{room}/availability', [AvailabilityController::class, 'update'])->name('availability.update');
    Route::get('/seasonal-prices', [SeasonalPriceController::class, 'index'])->name('seasonal-prices.index');
    Route::post('/rooms/{room}/seasonal-prices', [SeasonalPriceController::class, 'store'])->name('seasonal-prices.store');
    Route::delete('/seasonal-prices/{seasonalPrice}', [SeasonalPriceController::class, 'destroy'])->name('seasonal-prices.destroy');
});

Route::middleware(['auth','role:seller'])->prefix('nguoi-ban')->name('seller.')->group(function () {
    Route::get('/bang-dieu-khien', [DashboardController::class, 'seller'])->name('dashboard');
    Route::get('/orders', [ProductOrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/{order}/status', [ProductOrderController::class, 'updateStatus'])->name('orders.status');
    Route::resource('products', SellerProductController::class)->except(['show', 'destroy']);
});
