<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\WalletBalance;
use App\Models\WalletTransaction;
use App\Models\WithdrawRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletService
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    /**
     * Ambil atau buat wallet balance untuk user.
     */
    public function getBalance(int $userId): WalletBalance
    {
        return WalletBalance::firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0, 'frozen' => 0, 'total_income' => 0, 'total_withdrawn' => 0]
        );
    }

    /**
     * Credit pendapatan partner dari order.
     */
    public function creditPartnerEarning(int $partnerId, int $orderId, float $amount, string $description): WalletTransaction
    {
        return DB::transaction(function () use ($partnerId, $orderId, $amount, $description) {
            $wallet = $this->getBalance($partnerId);
            $balanceBefore = (float) $wallet->balance;

            $wallet->increment('balance', $amount);
            $wallet->increment('total_income', $amount);

            $wallet->refresh();
            $balanceAfter = (float) $wallet->balance;

            $transaction = WalletTransaction::create([
                'user_id' => $partnerId,
                'order_id' => $orderId,
                'type' => 'income',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $description,
                'reference' => "order_{$orderId}_income",
            ]);

            Log::info("Wallet credited for partner #{$partnerId}", [
                'amount' => $amount,
                'balance_after' => $balanceAfter,
                'order_id' => $orderId,
            ]);

            // Kirim notifikasi ke partner
            $user = User::find($partnerId);
            if ($user) {
                try {
                    $orderCode = Order::find($orderId)?->code ?? "#{$orderId}";
                    $this->notificationService->notifyWalletCredit($user, $amount, $orderCode);
                } catch (\Exception $e) {
                    Log::warning('Failed to send wallet credit notification', [
                        'partner_id' => $partnerId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return $transaction;
        });
    }

    /**
     * Top-up saldo wallet (untuk customer).
     */
    public function topUp(int $userId, float $amount, string $provider, string $transactionId): WalletTransaction
    {
        return DB::transaction(function () use ($userId, $amount, $provider, $transactionId) {
            $wallet = $this->getBalance($userId);
            $balanceBefore = (float) $wallet->balance;

            $wallet->increment('balance', $amount);

            $wallet->refresh();
            $balanceAfter = (float) $wallet->balance;

            $transaction = WalletTransaction::create([
                'user_id' => $userId,
                'type' => 'topup',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => "Top-up via {$provider}",
                'reference' => "topup_{$transactionId}",
            ]);

            Log::info("Wallet top-up for user #{$userId}", [
                'amount' => $amount,
                'provider' => $provider,
            ]);

            return $transaction;
        });
    }

    /**
     * Freeze saldo untuk withdraw request.
     */
    public function freezeForWithdraw(int $userId, float $amount): void
    {
        $wallet = $this->getBalance($userId);

        if ((float) $wallet->balance < $amount) {
            throw new \RuntimeException('Saldo tidak mencukupi untuk penarikan.');
        }

        $wallet->decrement('balance', $amount);
        $wallet->increment('frozen', $amount);

        Log::info('Wallet frozen for withdraw', [
            'user_id' => $userId,
            'amount' => $amount,
        ]);
    }

    /**
     * Unfreeze saldo (jika withdraw ditolak).
     */
    public function unfreeze(int $userId, float $amount): void
    {
        $wallet = $this->getBalance($userId);

        $wallet->increment('balance', $amount);
        $wallet->decrement('frozen', $amount);

        Log::info('Wallet unfrozen', [
            'user_id' => $userId,
            'amount' => $amount,
        ]);
    }

    /**
     * Konfirmasi withdraw selesai — kurangi frozen, tambah total_withdrawn.
     */
    public function confirmWithdraw(int $userId, float $amount): void
    {
        $wallet = $this->getBalance($userId);

        $wallet->decrement('frozen', $amount);
        $wallet->increment('total_withdrawn', $amount);

        Log::info('Withdraw confirmed', [
            'user_id' => $userId,
            'amount' => $amount,
        ]);
    }

    /**
     * Buat transaksi withdrawal record.
     */
    public function recordWithdrawal(int $userId, float $amount, string $description): WalletTransaction
    {
        $wallet = $this->getBalance($userId);
        $balanceBefore = (float) $wallet->balance;
        $balanceAfter = $balanceBefore; // Balance sudah dikurangi saat freeze

        return WalletTransaction::create([
            'user_id' => $userId,
            'type' => 'withdrawal',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'description' => $description,
            'reference' => 'withdraw_'.now()->timestamp,
        ]);
    }

    /**
     * Proses withdraw request oleh partner.
     */
    public function requestWithdraw(
        int $userId,
        float $amount,
        string $bankName,
        string $bankAccountNumber,
        string $bankAccountName,
    ): WithdrawRequest {
        $wallet = $this->getBalance($userId);

        if ((float) $wallet->balance < $amount) {
            throw new \RuntimeException('Saldo tidak mencukupi untuk penarikan.');
        }

        if ($amount <= 0) {
            throw new \RuntimeException('Jumlah penarikan harus lebih dari 0.');
        }

        return DB::transaction(function () use ($userId, $amount, $bankName, $bankAccountNumber, $bankAccountName) {
            // Freeze saldo
            $this->freezeForWithdraw($userId, $amount);

            // Buat request
            $request = WithdrawRequest::create([
                'user_id' => $userId,
                'amount' => $amount,
                'status' => 'pending',
                'bank_name' => $bankName,
                'bank_account_number' => $bankAccountNumber,
                'bank_account_name' => $bankAccountName,
            ]);

            Log::info('Withdraw request created', [
                'user_id' => $userId,
                'amount' => $amount,
                'request_id' => $request->id,
            ]);

            return $request;
        });
    }

    /**
     * Admin approve withdraw request.
     */
    public function approveWithdraw(WithdrawRequest $request): void
    {
        DB::transaction(function () use ($request) {
            $request->update([
                'status' => 'approved',
                'processed_at' => now(),
            ]);

            // Konfirmasi withdraw
            $this->confirmWithdraw($request->user_id, (float) $request->amount);

            // Record transaksi
            $this->recordWithdrawal(
                $request->user_id,
                (float) $request->amount,
                "Penarikan disetujui ke {$request->bank_name} ({$request->bank_account_number})"
            );

            Log::info('Withdraw approved', [
                'request_id' => $request->id,
                'user_id' => $request->user_id,
                'amount' => $request->amount,
            ]);

            // Kirim notifikasi ke partner
            $user = User::find($request->user_id);
            if ($user) {
                try {
                    $this->notificationService->notifyWithdrawApproved(
                        $user,
                        (float) $request->amount,
                        $request->bank_name
                    );
                } catch (\Exception $e) {
                    Log::warning('Failed to send withdraw approved notification', [
                        'request_id' => $request->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });
    }

    /**
     * Admin reject withdraw request.
     */
    public function rejectWithdraw(WithdrawRequest $request, string $reason): void
    {
        DB::transaction(function () use ($request, $reason) {
            // Unfreeze saldo
            $this->unfreeze($request->user_id, (float) $request->amount);

            $request->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
                'processed_at' => now(),
            ]);

            Log::info('Withdraw rejected', [
                'request_id' => $request->id,
                'user_id' => $request->user_id,
                'amount' => $request->amount,
                'reason' => $reason,
            ]);

            // Kirim notifikasi ke partner
            $user = User::find($request->user_id);
            if ($user) {
                try {
                    $this->notificationService->notifyWithdrawRejected(
                        $user,
                        (float) $request->amount,
                        $reason
                    );
                } catch (\Exception $e) {
                    Log::warning('Failed to send withdraw rejected notification', [
                        'request_id' => $request->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });
    }

    /**
     * Riwayat transaksi wallet.
     */
    public function getTransactions(int $userId, int $limit = 20): Collection
    {
        return WalletTransaction::where('user_id', $userId)
            ->with('order')
            ->latest()
            ->limit($limit)
            ->get();
    }
}
