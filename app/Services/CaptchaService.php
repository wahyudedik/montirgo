<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CaptchaService
{
    /**
     * Verifikasi reCAPTCHA v3 token terhadap Google API.
     *
     * @return array{success: bool, score: float|null, error: string|null}
     */
    public function verify(string $token, string $remoteIp = ''): array
    {
        $secretKey = config('recaptcha.secret_key');

        // Skip verifikasi jika secret key tidak dikonfigurasi (development mode)
        if (empty($secretKey)) {
            Log::debug('reCAPTCHA: Secret key not configured, skipping verification.');

            return [
                'success' => true,
                'score' => 1.0,
                'error' => null,
            ];
        }

        $minScore = (float) config('recaptcha.min_score', 0.5);
        $verifyUrl = config('recaptcha.verify_url', 'https://www.google.com/recaptcha/api/siteverify');

        try {
            $response = Http::timeout(5)->asForm()->post($verifyUrl, [
                'secret' => $secretKey,
                'response' => $token,
                'remoteip' => $remoteIp,
            ]);

            if (! $response->successful()) {
                Log::warning('reCAPTCHA: Google API returned HTTP '.$response->status());

                return [
                    'success' => false,
                    'score' => null,
                    'error' => 'CAPTCHA verification service unavailable',
                ];
            }

            $body = $response->json();
            $success = $body['success'] ?? false;
            $score = $body['score'] ?? null;
            $action = $body['action'] ?? null;
            $errorCodes = $body['error-codes'] ?? [];

            if (! $success) {
                Log::warning('reCAPTCHA: Verification failed', ['error_codes' => $errorCodes]);

                return [
                    'success' => false,
                    'score' => null,
                    'error' => 'CAPTCHA verification failed: '.implode(', ', $errorCodes),
                ];
            }

            if ($score !== null && $score < $minScore) {
                Log::warning('reCAPTCHA: Score below threshold', [
                    'score' => $score,
                    'min_score' => $minScore,
                    'action' => $action,
                ]);

                return [
                    'success' => false,
                    'score' => $score,
                    'error' => 'CAPTCHA score too low',
                ];
            }

            return [
                'success' => true,
                'score' => $score,
                'error' => null,
            ];

        } catch (\Exception $e) {
            Log::error('reCAPTCHA: Verification exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'score' => null,
                'error' => 'CAPTCHA verification failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Cek apakah CAPTCHA aktif (memiliki secret key).
     */
    public function isActive(): bool
    {
        return ! empty(config('recaptcha.secret_key'));
    }
}
