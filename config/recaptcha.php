<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Google reCAPTCHA v3 Configuration
    |--------------------------------------------------------------------------
    |
    | MontirGo menggunakan Google reCAPTCHA v3 untuk melindungi endpoint
    | register dan login dari bot/abuse. Di environment development tanpa
    | keys, verifikasi CAPTCHA akan di-skip (bypass).
    |
    */

    'site_key' => env('RECAPTCHA_SITE_KEY', ''),

    'secret_key' => env('RECAPTCHA_SECRET_KEY', ''),

    /*
    |------------------------------------------------------------------
    | Minimum Score Threshold
    |------------------------------------------------------------------
    | reCAPTCHA v3 mengembalikan skor 0.0 (bot) hingga 1.0 (manusia).
    | Semakin rendah threshold, semakin ketat filtering.
    | Default 0.5 — cukup untuk memblokir bot dasar.
    */
    'min_score' => (float) env('RECAPTCHA_MIN_SCORE', 0.5),

    /*
    |------------------------------------------------------------------
    | Verify URL
    |------------------------------------------------------------------
    */
    'verify_url' => 'https://www.google.com/recaptcha/api/siteverify',

];
