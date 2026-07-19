<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Midtrans Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk integrasi Midtrans Snap API (payment gateway).
    | MontirGo menggunakan Midtrans untuk memproses semua pembayaran
    | (callout fee + service fee) dari pelanggan.
    |
    | Sandbox: https://dashboard.sandbox.midtrans.com
    | Production: https://dashboard.midtrans.com
    |
    */

    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'server_key' => env('MIDTRANS_SERVER_KEY'),

    // Gunakan sandbox untuk development, false untuk production
    'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),

    // True untuk sandbox, false untuk production
    'is_sanitized' => (bool) env('MIDTRANS_IS_SANDBOX', true),

    /*
    |--------------------------------------------------------------------------
    | 3DS & Fraud Detection
    |--------------------------------------------------------------------------
    |
    | enable_3ds: Aktifkan 3D Secure untuk kartu kredit
    | fraud_detection: Aktifkan fraud detection Midtrans
    |
    */

    'enable_3ds' => (bool) env('MIDTRANS_ENABLE_3DS', true),
    'fraud_detection' => (bool) env('MIDTRANS_FRAUD_DETECTION', true),

    /*
    |--------------------------------------------------------------------------
    | Callback URLs
    |--------------------------------------------------------------------------
    |
    | URL yang akan di-redirect setelah pembayaran selesai/gagal.
    | frontend_finish_url: URL redirect setelah pembayaran selesai
    | frontend_unfinish_url: URL redirect jika pembayaran dibatalkan
    | frontend_error_url: URL redirect jika terjadi error
    |
    */

    'frontend_finish_url' => env('MIDTRANS_FRONTEND_FINISH_URL', '/customer/orders'),
    'frontend_unfinish_url' => env('MIDTRANS_FRONTEND_UNFINISH_URL', '/customer/orders'),
    'frontend_error_url' => env('MIDTRANS_FRONTEND_ERROR_URL', '/customer/orders'),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Snap API Base URL
    |--------------------------------------------------------------------------
    */

    'snap_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/v1'
        : 'https://app.sandbox.midtrans.com/snap/v1',

    'api_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://api.midtrans.com/v2'
        : 'https://api.sandbox.midtrans.com/v2',

];
