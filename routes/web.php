<?php

use App\Http\Controllers\Customer\ChatController;
use App\Http\Controllers\Customer\HistoryController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\Customer\SosController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Partner\OrderController as PartnerOrderController;
use App\Http\Controllers\Partner\ReviewController as PartnerReviewController;
use App\Http\Controllers\Partner\ServiceCostController;
use App\Http\Controllers\Partner\SparepartController;
use App\Http\Controllers\Partner\SubscriptionController;
use App\Http\Controllers\Partner\WalletController as PartnerWalletController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Customer Routes
Route::middleware(['auth', 'verified'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    // SOS Emergency
    Route::get('/sos', [SosController::class, 'index'])->name('sos.index');
    Route::post('/sos/send', [SosController::class, 'send'])->name('sos.send');
    Route::patch('/sos/{order}/cancel', [SosController::class, 'cancel'])->name('sos.cancel');

    // Chat
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/orders/{order}/chat', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/orders/{order}/chat/send', [ChatController::class, 'send'])->name('chat.send');
    Route::get('/orders/{order}/chat/poll', [ChatController::class, 'poll'])->name('chat.poll');

    // Reviews
    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::get('/orders/{order}/review', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/orders/{order}/review', [ReviewController::class, 'store'])->name('reviews.store');

    // Service History
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
});

// Partner Routes
Route::middleware(['auth', 'verified', 'role:partner'])->prefix('partner')->name('partner.')->group(function () {
    Route::get('/orders', [PartnerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [PartnerOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/accept', [PartnerOrderController::class, 'accept'])->name('orders.accept');
    Route::patch('/orders/{order}/reject', [PartnerOrderController::class, 'reject'])->name('orders.reject');
    Route::patch('/orders/{order}/status', [PartnerOrderController::class, 'updateStatus'])->name('orders.update-status');

    // Chat
    Route::get('/chat', [App\Http\Controllers\Partner\ChatController::class, 'index'])->name('chat.index');
    Route::get('/orders/{order}/chat', [App\Http\Controllers\Partner\ChatController::class, 'show'])->name('chat.show');
    Route::post('/orders/{order}/chat/send', [App\Http\Controllers\Partner\ChatController::class, 'send'])->name('chat.send');
    Route::get('/orders/{order}/chat/poll', [App\Http\Controllers\Partner\ChatController::class, 'poll'])->name('chat.poll');

    // Partner Wallet
    Route::get('/wallet', [PartnerWalletController::class, 'index'])->name('wallet.index');
    Route::get('/wallet/withdraw', [PartnerWalletController::class, 'withdrawForm'])->name('wallet.withdraw');
    Route::post('/wallet/withdraw', [PartnerWalletController::class, 'withdraw'])->name('wallet.withdraw.store');
    Route::get('/wallet/history', [PartnerWalletController::class, 'history'])->name('wallet.history');

    // Reviews
    Route::get('/reviews', [PartnerReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews/{review}/reply', [PartnerReviewController::class, 'reply'])->name('reviews.reply');

    // Subscription
    Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/subscription', [SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
    Route::delete('/subscription', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');

    // Spareparts
    Route::resource('spareparts', SparepartController::class)->except(['show']);

    // Service Cost Input
    Route::get('/service-cost', [ServiceCostController::class, 'index'])->name('service-cost.index');
    Route::get('/orders/{order}/service-cost', [ServiceCostController::class, 'create'])->name('service-cost.create');
    Route::post('/orders/{order}/service-cost', [ServiceCostController::class, 'store'])->name('service-cost.store');

    // Partner Upload Foto Before/After
    Route::post('/orders/{order}/photos', [PartnerOrderController::class, 'uploadPhoto'])->name('orders.upload-photo');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
