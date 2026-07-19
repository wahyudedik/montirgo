<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use RuntimeException;

class MidtransService
{
    private string $clientKey;

    private string $serverKey;

    private bool $isProduction;

    private string $snapUrl;

    private string $apiUrl;

    public function __construct()
    {
        $this->clientKey = (string) config('midtrans.client_key');
        $this->serverKey = (string) config('midtrans.server_key');
        $this->isProduction = (bool) config('midtrans.is_production');
        $this->snapUrl = (string) config('midtrans.snap_url');
        $this->apiUrl = (string) config('midtrans.api_url');

        // Set Midtrans SDK config
        Config::$clientKey = $this->clientKey;
        Config::$serverKey = $this->serverKey;
        Config::$isProduction = $this->isProduction;
        Config::$isSanitized = (bool) config('midtrans.is_sanitized');
        Config::$is3ds = (bool) config('midtrans.enable_3ds');
    }

    /**
     * Buat Snap Token untuk pembayaran order.
     *
     * Menggunakan Midtrans Snap API untuk menghasilkan token
     * yang digunakan mobile app/frontend membuka halaman pembayaran.
     *
     * @return array{token: string, redirect_url: string}
     */
    public function createPaymentToken(Order $order, Payment $payment): array
    {
        $customerDetails = [
            'first_name' => $order->user->name ?? 'Customer',
            'email' => $order->user->email ?? 'customer@montirgo.com',
            'phone' => $order->user->phone ?? '',
        ];

        $itemDetails = $this->buildItemDetails($order, $payment);

        $params = [
            'transaction_details' => [
                'order_id' => $order->code,
                'gross_amount' => (float) $payment->amount,
            ],
            'customer_details' => $customerDetails,
            'item_details' => $itemDetails,
            'callbacks' => [
                'finish' => config('midtrans.frontend_finish_url'),
                'unfinish' => config('midtrans.frontend_unfinish_url'),
                'error' => config('midtrans.frontend_error_url'),
            ],
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit' => 'minute',
                'duration' => 60, // Expired dalam 60 menit
            ],
            'enabled_payments' => $this->getEnabledPayments($order->payment_method),
        ];

        // Tambahkan custom expiry untuk order
        $params['custom_field1'] = $order->code;
        $params['custom_field2'] = (string) $order->id;
        $params['custom_field3'] = 'montirgo';

        try {
            Config::$curlOptions = [
                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_TIMEOUT => 30,
            ];

            $transaction = Snap::createTransaction($params);
            $token = $transaction->token;
            $redirectUrl = $transaction->redirect_url;

            // Simpan token di metadata payment
            $payment->update([
                'metadata' => array_merge($payment->metadata ?? [], [
                    'snap_token' => $token,
                    'redirect_url' => $redirectUrl,
                    'created_at' => now()->toIso8601String(),
                ]),
            ]);

            Log::info("Midtrans Snap Token created for order #{$order->code}", [
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'method' => $order->payment_method,
            ]);

            return [
                'token' => $token,
                'redirect_url' => $redirectUrl,
            ];
        } catch (RuntimeException $e) {
            Log::error("Midtrans Snap Token creation failed for order #{$order->code}", [
                'error' => $e->getMessage(),
                'amount' => $payment->amount,
            ]);

            throw new RuntimeException('Gagal membuat token pembayaran: '.$e->getMessage(), 500, $e);
        }
    }

    /**
     * Verifikasi signature callback dari Midtrans webhook.
     */
    public function verifyCallbackSignature(array $notification): bool
    {
        $orderId = $notification['order_id'] ?? '';
        $statusCode = $notification['status_code'] ?? '';
        $grossAmount = $notification['gross_amount'] ?? '';
        $serverKey = $this->serverKey;

        $signatureKey = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);
        $notificationSignature = $notification['signature_key'] ?? '';

        return hash_equals($signatureKey, $notificationSignature);
    }

    /**
     * Cek status transaksi dari Midtrans API.
     *
     * @return array{transaction_status: string, fraud_status: string|null, payment_type: string|null, status_code: string}
     */
    public function getTransactionStatus(string $orderId): array
    {
        $transaction = new Transaction;
        $status = $transaction->status($orderId);

        return [
            'transaction_status' => $status['transaction_status'] ?? 'unknown',
            'fraud_status' => $status['fraud_status'] ?? null,
            'payment_type' => $status['payment_type'] ?? null,
            'status_code' => $status['status_code'] ?? '',
            'transaction_id' => $status['transaction_id'] ?? null,
            'settlement_time' => $status['settlement_time'] ?? null,
            'payment_amount' => $status['gross_amount'] ?? null,
        ];
    }

    /**
     * Map Midtrans transaction_status ke internal payment status.
     */
    public function mapTransactionStatus(string $midtransStatus, ?string $fraudStatus = null): string
    {
        // Jika fraud status adalah deny, langsung gagal
        if ($fraudStatus === 'deny') {
            return 'failed';
        }

        return match ($midtransStatus) {
            'capture' => 'paid',
            'settlement' => 'paid',
            'pending' => 'pending',
            'deny' => 'failed',
            'expire' => 'failed',
            'cancel' => 'cancelled',
            'refund' => 'refunded',
            'partial_refund' => 'refunded',
            default => 'pending',
        };
    }

    /**
     * Proses refund melalui Midtrans API.
     */
    public function processRefund(string $orderId, float $amount, string $reason): array
    {
        try {
            $transaction = new Transaction;
            $result = $transaction->refund($orderId, [
                'reason' => $reason,
                'refund_amount' => (string) $amount,
            ]);

            Log::info("Midtrans refund processed for order #{$orderId}", [
                'amount' => $amount,
                'reason' => $reason,
            ]);

            return $result;
        } catch (RuntimeException $e) {
            Log::error("Midtrans refund failed for order #{$orderId}", [
                'error' => $e->getMessage(),
                'amount' => $amount,
            ]);

            throw new RuntimeException('Gagal memproses refund: '.$e->getMessage(), 500, $e);
        }
    }

    /**
     * Build item details untuk Midtrans.
     */
    private function buildItemDetails(Order $order, Payment $payment): array
    {
        $items = [
            [
                'id' => 'callout_fee',
                'price' => (float) $payment->metadata['callout_fee'] ?? 30000,
                'quantity' => 1,
                'name' => 'Biaya Panggilan (Callout Fee)',
            ],
        ];

        $serviceFee = (float) ($payment->metadata['service_fee'] ?? 0);
        if ($serviceFee > 0) {
            $items[] = [
                'id' => 'service_fee',
                'price' => $serviceFee,
                'quantity' => 1,
                'name' => 'Biaya Servis & Sparepart',
            ];
        }

        return $items;
    }

    /**
     * Daftar metode pembayaran yang diaktifkan berdasarkan payment method user.
     */
    private function getEnabledPayments(string $paymentMethod): array
    {
        return match ($paymentMethod) {
            'qris' => ['qris'],
            'ewallet' => ['gopay', 'shopeepay', 'dana', 'ovo'],
            'bank_transfer' => ['bca', 'bni', 'bri', 'mandiri', 'permata', 'other_va'],
            default => ['qris', 'gopay', 'shopeepay', 'dana', 'bca', 'bni', 'bri', 'mandiri'],
        };
    }

    /**
     * Cek apakah Midtrans SDK terkonfigurasi dengan benar.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->clientKey) && ! empty($this->serverKey);
    }
}
