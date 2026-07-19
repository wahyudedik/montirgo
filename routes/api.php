<?php

use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AdminPartnerController;
use App\Http\Controllers\Api\AdvertisementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\InsuranceController;
use App\Http\Controllers\Api\MechanicController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PartnerController;
use App\Http\Controllers\Api\PartnerServiceController;
use App\Http\Controllers\Api\PaymentGatewayController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SparepartController;
use App\Http\Controllers\Api\SymptomController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\WalletController;
use App\Models\NotificationLog;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ─── Public Routes ─────────────────────────────────────
Route::post('/v1/auth/register', [AuthController::class, 'register'])
    ->middleware('throttle:auth-strict');
Route::post('/v1/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:auth');

// Partner nearby (public, default api throttle)
Route::get('/v1/partners/nearby', [PartnerController::class, 'nearby'])
    ->middleware('throttle:api');

// Insurance partners (public, default api throttle)
Route::get('/v1/insurance/partners', [InsuranceController::class, 'partners'])
    ->middleware('throttle:api');

// Partner reviews (public, default api throttle)
Route::get('/v1/partners/{partner}/reviews', [ReviewController::class, 'partnerReviews'])
    ->middleware('throttle:api');

// ─── Protected Routes (Sanctum) ────────────────────────
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::patch('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/location', [AuthController::class, 'updateLocation']);
    Route::delete('/auth/account', [AuthController::class, 'deleteAccount']);
    Route::get('/auth/profile-completion', [AuthController::class, 'profileCompletion']);

    // Orders — Customer
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store'])
        ->middleware('throttle:order-create');
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel']);

    // SOS Emergency
    Route::post('/sos', [OrderController::class, 'sosStore'])
        ->middleware('throttle:sos');

    // Orders — Partner
    Route::get('/partner/orders', [OrderController::class, 'partnerOrders']);
    Route::patch('/partner/orders/{order}/accept', [OrderController::class, 'accept']);
    Route::patch('/partner/orders/{order}/reject', [OrderController::class, 'reject']);
    Route::patch('/partner/orders/{order}/status', [OrderController::class, 'updateStatus']);

    // Chat
    Route::get('/orders/{order}/chat', [ChatController::class, 'show'])
        ->middleware('throttle:chat');
    Route::post('/orders/{order}/chat/send', [ChatController::class, 'send'])
        ->middleware('throttle:chat');
    Route::get('/orders/{order}/chat/poll', [ChatController::class, 'poll'])
        ->middleware('throttle:chat');

    // Partner Profile
    Route::get('/partner/profile', [PartnerController::class, 'profile']);
    Route::patch('/partner/profile', [PartnerController::class, 'updateProfile']);
    Route::post('/partner/toggle-online', [PartnerController::class, 'toggleOnline']);
    Route::post('/partner/toggle-availability', [PartnerController::class, 'toggleAvailability']);
    Route::post('/partner/location', [PartnerController::class, 'updateLocation']);
    Route::get('/partner/orders/{order}/track', [PartnerController::class, 'trackOrder']);
    Route::get('/partner/stats', [PartnerController::class, 'stats']);

    // Partner Status — Granular status management
    Route::patch('/partner/status', [PartnerController::class, 'updateStatus']);

    // Partner Profile Completion
    Route::get('/partner/profile-completion', [PartnerController::class, 'profileCompletion']);

    // Partner Mechanics (Multiple mechanics per workshop)
    Route::get('/partner/mechanics', [MechanicController::class, 'index']);
    Route::post('/partner/mechanics', [MechanicController::class, 'store']);
    Route::patch('/partner/mechanics/{mechanic}', [MechanicController::class, 'update']);
    Route::delete('/partner/mechanics/{mechanic}', [MechanicController::class, 'destroy']);

    // Symptoms — Diagnosis Wizard (public for listing, filtered by vehicle_category)
    Route::get('/symptoms', [SymptomController::class, 'index']);

    // Vehicles — Customer
    Route::apiResource('vehicles', VehicleController::class)->except(['edit', 'create']);
    Route::patch('/vehicles/{vehicle}/default', [VehicleController::class, 'setDefault']);

    // Reviews — Customer
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/reviews/stats', [ReviewController::class, 'stats']);

    // Reviews — Partner
    Route::get('/partner/reviews', [ReviewController::class, 'partnerIndex']);
    Route::post('/reviews/{review}/reply', [ReviewController::class, 'reply']);

    // Review Stats (inside auth for consistency)
    Route::get('/reviews/stats', [ReviewController::class, 'stats']);

    // Partner Services
    Route::get('/partner/services', [PartnerServiceController::class, 'index']);
    Route::post('/partner/services', [PartnerServiceController::class, 'store']);
    Route::patch('/partner/services/{service}', [PartnerServiceController::class, 'update']);
    Route::delete('/partner/services/{service}', [PartnerServiceController::class, 'destroy']);
    Route::patch('/partner/services/{service}/toggle', [PartnerServiceController::class, 'toggleActive']);

    // Partner Spareparts
    Route::get('/partner/spareparts', [SparepartController::class, 'index']);
    Route::post('/partner/spareparts', [SparepartController::class, 'store']);
    Route::get('/partner/spareparts/{sparepart}', [SparepartController::class, 'show']);
    Route::patch('/partner/spareparts/{sparepart}', [SparepartController::class, 'update']);
    Route::delete('/partner/spareparts/{sparepart}', [SparepartController::class, 'destroy']);
    Route::patch('/partner/spareparts/{sparepart}/toggle', [SparepartController::class, 'toggleActive']);

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
        $request->validate([
            'fcm_token' => 'required|string',
            'device_name' => 'nullable|string|max:100',
            'platform' => 'nullable|string|in:android,ios,web',
        ]);

        /** @var User $user */
        $user = $request->user();

        // Register ke tabel multi-device
        $user->registerFcmToken(
            $request->fcm_token,
            $request->input('device_name'),
            $request->input('platform'),
        );

        // Backward compat: update legacy field juga
        $user->update([
            'fcm_token' => $request->fcm_token,
            'last_active_at' => now(),
        ]);

        return response()->json(['success' => true]);
    });

    // Hapus FCM token (saat logout)
    Route::delete('/fcm-token', function (Request $request) {
        $request->validate(['fcm_token' => 'required|string']);

        /** @var User $user */
        $user = $request->user();

        // Hapus dari tabel multi-device
        $user->removeFcmToken($request->fcm_token);

        // Jika token yang dihapus sama dengan legacy field, clear juga
        if ($user->fcm_token === $request->fcm_token) {
            $user->update(['fcm_token' => null]);
        }

        return response()->json(['success' => true]);
    });

    // Notification Preferences
    Route::get('/notification-preferences', function (Request $request) {
        $defaultPrefs = [
            'push_enabled' => true,
            'chat' => true,
            'order_status' => true,
            'payment' => true,
            'new_order' => true,
        ];

        return response()->json([
            'preferences' => $request->user()->notification_preferences ?? $defaultPrefs,
        ]);
    });

    Route::patch('/notification-preferences', function (Request $request) {
        $validated = $request->validate([
            'push_enabled' => 'sometimes|boolean',
            'chat' => 'sometimes|boolean',
            'order_status' => 'sometimes|boolean',
            'payment' => 'sometimes|boolean',
            'new_order' => 'sometimes|boolean',
        ]);

        $defaultPrefs = [
            'push_enabled' => true,
            'chat' => true,
            'order_status' => true,
            'payment' => true,
            'new_order' => true,
        ];

        $current = $request->user()->notification_preferences ?? $defaultPrefs;
        $updated = array_merge($current, $validated);

        $request->user()->update(['notification_preferences' => $updated]);

        return response()->json([
            'success' => true,
            'preferences' => $updated,
        ]);
    });

    // Admin Dashboard
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard/stats', [AdminDashboardController::class, 'stats']);
        Route::get('/dashboard/revenue-chart', [AdminDashboardController::class, 'revenueChart']);
        Route::get('/dashboard/order-status', [AdminDashboardController::class, 'orderStatusDistribution']);
        Route::get('/dashboard/top-partners', [AdminDashboardController::class, 'topPartners']);

        // Admin Partner Management
        Route::get('/partners', [AdminPartnerController::class, 'index']);
        Route::get('/partners/{partner}', [AdminPartnerController::class, 'show']);
        Route::patch('/partners/{partner}/approve', [AdminPartnerController::class, 'approve']);
        Route::patch('/partners/{partner}/reject', [AdminPartnerController::class, 'reject']);
        Route::patch('/partners/{partner}/suspend', [AdminPartnerController::class, 'suspend']);
    });

    // Wallet
    Route::get('/wallet', [WalletController::class, 'index']);
    Route::get('/wallet/transactions', [WalletController::class, 'transactions']);
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])
        ->middleware('throttle:payment');
    Route::get('/wallet/withdraw/history', [WalletController::class, 'withdrawHistory']);

    // Insurance Claims
    Route::post('/orders/{order}/insurance-claim', [InsuranceController::class, 'createClaim']);
    Route::get('/insurance-claims/{claim}/status', [InsuranceController::class, 'claimStatus']);

    // Payment Gateway — Customer create payment (Snap Token)
    Route::post('/orders/{order}/pay', [PaymentGatewayController::class, 'createPayment'])
        ->middleware('throttle:payment');

    // Payment Gateway — Partner create service fee payment (Snap Token)
    Route::post('/partner/orders/{order}/service-fee-pay', [PaymentGatewayController::class, 'createServiceFeePayment'])
        ->middleware('throttle:payment');
});

// Insurance Webhook (authenticated via API key, rate limited)
Route::post('/v1/insurance/webhook/claim-update', [InsuranceController::class, 'webhookUpdateClaim'])
    ->middleware('throttle:webhook');

// Advertisements — Mobile (public, rate limited)
Route::get('/v1/ads', [AdvertisementController::class, 'index'])
    ->middleware('throttle:ads');
Route::get('/v1/ads/{advertisement}', [AdvertisementController::class, 'show'])
    ->middleware('throttle:ads');
Route::post('/v1/ads/{advertisement}/impression', [AdvertisementController::class, 'trackImpression'])
    ->middleware('throttle:ads');
Route::post('/v1/ads/{advertisement}/click', [AdvertisementController::class, 'trackClick'])
    ->middleware('throttle:ads');

// Payment Gateway Webhook (public endpoint for payment provider callbacks, rate limited)
Route::post('/v1/payment/webhook', [PaymentGatewayController::class, 'handlePaymentWebhook'])
    ->middleware('throttle:webhook');
Route::get('/v1/payment/status/{orderCode}', [PaymentGatewayController::class, 'paymentStatus']);
