<?php

use App\Http\Controllers\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Admin\HomestayController;
use App\Http\Controllers\Admin\PaymentOverviewController as AdminPaymentOverviewController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\RoomController as AdminRoomController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExperienceBookingPaymentController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Host\AvailabilityController;
use App\Http\Controllers\Host\BookingController as HostBookingController;
use App\Http\Controllers\Host\ExperienceController as HostExperienceController;
use App\Http\Controllers\Host\RoomController;
use App\Http\Controllers\Host\SeasonalPriceController;
use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Product\LocalProductController;
use App\Http\Controllers\ProductOrderController;
use App\Http\Controllers\ProductOrderPaymentController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RoomDetailController;
use App\Http\Controllers\RoomChatbotController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Seller\ProductController as SellerProductController;
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
Route::post('/chatbot/goi-y-phong', RoomChatbotController::class)
    ->middleware('throttle:room-chatbot')
    ->name('chatbot.rooms');

Route::middleware('guest')->group(function () {
    Route::get('/quen-mat-khau', [\App\Http\Controllers\AccountController::class, 'forgot'])->name('password.request');
    Route::post('/quen-mat-khau', [\App\Http\Controllers\AccountController::class, 'email'])->middleware('throttle:recovery')->name('password.email');
    Route::get('/dat-lai-mat-khau/{token}', [\App\Http\Controllers\AccountController::class, 'resetForm'])->name('password.reset');
    Route::post('/dat-lai-mat-khau', [\App\Http\Controllers\AccountController::class, 'reset'])->middleware('throttle:recovery')->name('password.update');
    Route::get('/dang-nhap', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.submit');
    Route::get('/dang-ky', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/dang-ky', [AuthController::class, 'register'])->middleware('throttle:login')->name('register.submit');
});
Route::get('/verification-documents/{verification}', [\App\Http\Controllers\Admin\VerificationController::class, 'document'])->middleware('auth')->name('verification.document');
Route::post('/dang-xuat', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::post('/bookings', [BookingController::class, 'store'])->middleware(['auth', 'role:guest'])->name('bookings.store');
Route::post('/trai-nghiem/{experience}/dat-cho', [ExperienceController::class, 'book'])->middleware(['auth', 'role:guest'])->name('experiences.book');
Route::middleware(['auth'])->prefix('tai-khoan')->name('account.')->group(function () {
    Route::get('/profile', [\App\Http\Controllers\AccountController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\AccountController::class, 'profile'])->name('profile.update');
    Route::put('/password', [\App\Http\Controllers\AccountController::class, 'password'])->name('password.update');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::get('/loyalty', [LoyaltyController::class, 'index'])->name('loyalty.index');
});
Route::middleware(['auth', 'role:guest'])->prefix('tai-khoan')->name('guest.')->group(function () {
    Route::get('/cart', [\App\Http\Controllers\CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [\App\Http\Controllers\CartController::class, 'store'])->block(15, 15)->name('cart.store');
    Route::put('/cart', [\App\Http\Controllers\CartController::class, 'update'])->block(15, 15)->name('cart.update');
    Route::post('/cart/checkout', [\App\Http\Controllers\CartController::class, 'checkout'])->block(15, 15)->name('cart.checkout');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings/{booking}/cancel', [\App\Http\Controllers\BookingLifecycleController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{booking}/refund', [\App\Http\Controllers\BookingLifecycleController::class, 'refund'])->name('bookings.refund');
    Route::get('/bookings/{booking}/payment', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('/bookings/{booking}/payments/vnpay', [PaymentController::class, 'createVnPay'])->name('payments.vnpay');
    Route::post('/bookings/{booking}/payments/cash', [PaymentController::class, 'store'])->name('payments.cash');
    Route::post('/bookings/{booking}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::post('/orders/{order}/status', [ProductOrderController::class, 'updateStatus'])->name('orders.status');
    Route::get('/orders', [ProductOrderController::class, 'index'])->name('orders.index');
    Route::post('/orders', [ProductOrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}/payment', [ProductOrderPaymentController::class, 'show'])->name('order-payments.show');
    Route::post('/orders/{order}/payments/cash', [ProductOrderPaymentController::class, 'store'])->name('order-payments.cash');
    Route::get('/experience-bookings', [\App\Http\Controllers\ExperienceBookingController::class, 'index'])->name('experience-bookings.index');
    Route::post('/experience-bookings/{experienceBooking}/cancel', [\App\Http\Controllers\ExperienceBookingController::class, 'cancel'])->name('experience-bookings.cancel');
    Route::get('/experience-bookings/{experienceBooking}/payment', [ExperienceBookingPaymentController::class, 'show'])->name('experience-payments.show');
    Route::post('/experience-bookings/{experienceBooking}/payments/cash', [ExperienceBookingPaymentController::class, 'store'])->name('experience-payments.cash');
});

Route::middleware(['auth', 'role:admin'])->prefix('quan-tri')->name('admin.')->group(function () {
    Route::post('/payments/{payment}/reconcile', \App\Http\Controllers\Admin\PaymentReconciliationController::class)->name('payments.reconcile');
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->except(['show', 'destroy']);
    Route::get('/reports/bookings.csv', \App\Http\Controllers\Admin\BookingReportController::class)->name('reports.bookings');
    Route::get('/bang-dieu-khien', [DashboardController::class, 'admin'])->name('dashboard');
    Route::resource('homestays', HomestayController::class)->except(['show']);
    Route::resource('rooms', AdminRoomController::class)->except(['show']);
    Route::resource('products', AdminProductController::class)->except(['show']);
    Route::post('/bookings/{booking}/refund', [\App\Http\Controllers\BookingLifecycleController::class, 'refund'])->name('bookings.refund');
    Route::get('/payments', [AdminPaymentOverviewController::class, 'index'])->name('payments.index');
    Route::get('/verifications', [\App\Http\Controllers\Admin\VerificationController::class, 'index'])->name('verifications.index');
    Route::post('/verifications/{verification}/review', [\App\Http\Controllers\Admin\VerificationController::class, 'review'])->name('verifications.review');
    Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');
});

Route::middleware(['auth', 'role:host'])->prefix('chu-homestay')->name('host.')->group(function () {
    Route::get('/bang-dieu-khien', [DashboardController::class, 'host'])->name('dashboard');
    Route::resource('homestays', \App\Http\Controllers\Host\HomestayController::class)->except(['show', 'destroy']);
    Route::resource('rooms', RoomController::class)->except(['show', 'destroy']);
    Route::post('/bookings/{booking}/complete', [\App\Http\Controllers\BookingLifecycleController::class, 'complete'])->name('bookings.complete');
    Route::get('/bookings', [HostBookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings/{booking}/cash/received', [\App\Http\Controllers\CashReceiptController::class, 'booking'])->name('bookings.cash-received');
    Route::post('/bookings/{booking}/cash/refunded', [\App\Http\Controllers\CashReceiptController::class, 'refundBooking'])->name('bookings.cash-refunded');
    Route::post('/experience-bookings/{experienceBooking}/cash/received', [\App\Http\Controllers\CashReceiptController::class, 'experience'])->name('experience-bookings.cash-received');
    Route::post('/experience-bookings/{experienceBooking}/cash/refunded', [\App\Http\Controllers\CashReceiptController::class, 'refundExperience'])->name('experience-bookings.cash-refunded');
    Route::get('/experience-bookings', [\App\Http\Controllers\ExperienceBookingController::class, 'index'])->name('experience-bookings.index');
    Route::post('/experience-bookings/{experienceBooking}/complete', [\App\Http\Controllers\ExperienceBookingController::class, 'complete'])->name('experience-bookings.complete');
    Route::resource('experiences', HostExperienceController::class)->except(['show']);
    Route::get('/availability', [AvailabilityController::class, 'index'])->name('availability.index');
    Route::post('/rooms/{room}/availability', [AvailabilityController::class, 'update'])->name('availability.update');
    Route::get('/seasonal-prices', [SeasonalPriceController::class, 'index'])->name('seasonal-prices.index');
    Route::post('/rooms/{room}/seasonal-prices', [SeasonalPriceController::class, 'store'])->name('seasonal-prices.store');
    Route::delete('/seasonal-prices/{seasonalPrice}', [SeasonalPriceController::class, 'destroy'])->name('seasonal-prices.destroy');
});

Route::middleware(['auth', 'role:seller'])->prefix('nguoi-ban')->name('seller.')->group(function () {
    Route::get('/bang-dieu-khien', [DashboardController::class, 'seller'])->name('dashboard');
    Route::post('/orders/{order}/cash/received', [\App\Http\Controllers\CashReceiptController::class, 'order'])->name('orders.cash-received');
    Route::post('/orders/{order}/cash/refunded', [\App\Http\Controllers\CashReceiptController::class, 'refundOrder'])->name('orders.cash-refunded');
    Route::get('/orders', [ProductOrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/{order}/status', [ProductOrderController::class, 'updateStatus'])->name('orders.status');
    Route::resource('products', SellerProductController::class)->except(['show', 'destroy']);
});
