<?php

use App\Http\Controllers\Admin\AdvertisementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\InsuranceController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PartnerController;
use App\Http\Controllers\Api\PaymentGatewayController;
use App\Http\Controllers\Api\WalletController;
use App\Models\NotificationLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ─── Public Routes ─────────────────────────────────────
Route::post('/v1/auth/register', [AuthController::class, 'register']);
Route::post('/v1/auth/login', [AuthController::class, 'login']);

// Partner nearby (public)
Route::get('/v1/partners/nearby', [PartnerController::class, 'nearby']);

// Insurance partners (public)
Route::get('/v1/insurance/partners', [InsuranceController::class, 'partners']);

// ─── Protected Routes (Sanctum) ────────────────────────
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::patch('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/location', [AuthController::class, 'updateLocation']);

    // Orders — Customer
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel']);

    // SOS Emergency
    Route::post('/sos', [OrderController::class, 'sosStore']);

    // Orders — Partner
    Route::get('/partner/orders', [OrderController::class, 'partnerOrders']);
    Route::patch('/partner/orders/{order}/accept', [OrderController::class, 'accept']);
    Route::patch('/partner/orders/{order}/reject', [OrderController::class, 'reject']);
    Route::patch('/partner/orders/{order}/status', [OrderController::class, 'updateStatus']);

    // Chat
    Route::get('/orders/{order}/chat', [ChatController::class, 'show']);
    Route::post('/orders/{order}/chat/send', [ChatController::class, 'send']);
    Route::get('/orders/{order}/chat/poll', [ChatController::class, 'poll']);

    // Partner Profile
    Route::get('/partner/profile', [PartnerController::class, 'profile']);
    Route::patch('/partner/profile', [PartnerController::class, 'updateProfile']);
    Route::post('/partner/toggle-online', [PartnerController::class, 'toggleOnline']);
    Route::post('/partner/toggle-availability', [PartnerController::class, 'toggleAvailability']);
    Route::post('/partner/location', [PartnerController::class, 'updateLocation']);
    Route::get('/partner/orders/{order}/track', [PartnerController::class, 'trackOrder']);

    // Notifications
    Route::get('/notifications', function (Request $request) {
        $service = app(NotificationService::class);
        $unreadCount = $service->getUnreadCount($request->user());
        $notifications = NotificationLog::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'unread_count' => $unreadCount,
            'data' => $notifications,
        ]);
    });
    Route::post('/notifications/read-all', function (Request $request) {
        $service = app(NotificationService::class);
        $service->markAllAsRead($request->user());

        return response()->json(['success' => true]);
    });
    Route::post('/fcm-token', function (Request $request) {
        $request->validate(['fcm_token' => 'required|string']);
        $request->user()->update(['fcm_token' => $request->fcm_token]);

        return response()->json(['success' => true]);
    });

    // Wallet
    Route::get('/wallet', [WalletController::class, 'index']);
    Route::get('/wallet/transactions', [WalletController::class, 'transactions']);
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw']);
    Route::get('/wallet/withdraw/history', [WalletController::class, 'withdrawHistory']);

    // Insurance Claims
    Route::post('/orders/{order}/insurance-claim', [InsuranceController::class, 'createClaim']);
    Route::get('/insurance-claims/{claim}/status', [InsuranceController::class, 'claimStatus']);
});

// Insurance Webhook (authenticated via API key)
Route::post('/v1/insurance/webhook/claim-update', [InsuranceController::class, 'webhookUpdateClaim']);

// Advertisement Tracking (public)
Route::post('/v1/ads/{advertisement}/impression', [AdvertisementController::class, 'trackImpression']);
Route::post('/v1/ads/{advertisement}/click', [AdvertisementController::class, 'trackClick']);

// Payment Gateway Webhook (public endpoint for payment provider callbacks)
Route::post('/v1/payment/webhook', [PaymentGatewayController::class, 'webhookUpdateClaim']);
Route::get('/v1/payment/status/{orderCode}', [PaymentGatewayController::class, 'paymentStatus']);
