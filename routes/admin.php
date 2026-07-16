<?php

use App\Http\Controllers\Admin\AdvertisementController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PartnerController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WithdrawController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', UserController::class)->except(['show', 'store', 'create']);
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

    Route::resource('partners', PartnerController::class)->except(['store', 'create']);
    Route::patch('partners/{partner}/approve', [PartnerController::class, 'approve'])->name('partners.approve');
    Route::patch('partners/{partner}/reject', [PartnerController::class, 'reject'])->name('partners.reject');
    Route::patch('partners/{partner}/suspend', [PartnerController::class, 'suspend'])->name('partners.suspend');

    Route::resource('orders', OrderController::class)->only(['index', 'show']);

    // Withdraw Management
    Route::get('withdraws', [WithdrawController::class, 'index'])->name('withdraws.index');
    Route::get('withdraws/{withdrawRequest}', [WithdrawController::class, 'show'])->name('withdraws.show');
    Route::patch('withdraws/{withdrawRequest}/approve', [WithdrawController::class, 'approve'])->name('withdraws.approve');
    Route::patch('withdraws/{withdrawRequest}/reject', [WithdrawController::class, 'reject'])->name('withdraws.reject');

    // Advertisement Management
    Route::resource('advertisements', AdvertisementController::class);
});
