<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Review;
use App\Observers\OrderObserver;
use App\Observers\PaymentObserver;
use App\Observers\ReviewObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
        $this->configureObservers();
    }

    /**
     * Register Eloquent observers for side effects.
     */
    private function configureObservers(): void
    {
        Order::observe(OrderObserver::class);
        Payment::observe(PaymentObserver::class);
        Review::observe(ReviewObserver::class);
    }

    /**
     * Configure rate limiters for the application.
     */
    private function configureRateLimiting(): void
    {
        // Default API limiter: 60 requests per minute per user/IP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Auth limiter: 10 attempts per minute (login/register)
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        // Strict auth limiter: 5 attempts per minute (register)
        RateLimiter::for('auth-strict', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // SOS emergency: 5 per minute per user (prevent spam)
        RateLimiter::for('sos', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        // Payment actions: 10 per minute per user
        RateLimiter::for('payment', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // Order creation: 5 per minute per user
        RateLimiter::for('order-create', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        // Chat: 30 per minute per user
        RateLimiter::for('chat', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        // Webhook: 30 per minute by IP (external services)
        RateLimiter::for('webhook', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        // Ad tracking: 30 per minute per IP
        RateLimiter::for('ads', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });
    }
}
