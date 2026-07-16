<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WalletResource;
use App\Http\Resources\WalletTransactionResource;
use App\Http\Resources\WithdrawRequestResource;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WalletController extends Controller
{
    /**
     * Saldo wallet.
     */
    public function index(Request $request): WalletResource
    {
        $wallet = app(WalletService::class)->getBalance($request->user()->id);

        return new WalletResource($wallet);
    }

    /**
     * Riwayat transaksi.
     */
    public function transactions(Request $request): AnonymousResourceCollection
    {
        $transactions = app(WalletService::class)
            ->getTransactions($request->user()->id, 20);

        return WalletTransactionResource::collection($transactions);
    }

    /**
     * Request withdraw.
     */
    public function withdraw(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:10000', 'max:50000000'],
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_number' => ['required', 'string', 'max:30'],
            'bank_account_name' => ['required', 'string', 'max:100'],
        ]);

        try {
            $withdrawRequest = app(WalletService::class)->requestWithdraw(
                $request->user()->id,
                (float) $validated['amount'],
                $validated['bank_name'],
                $validated['bank_account_number'],
                $validated['bank_account_name'],
            );

            return response()->json([
                'message' => 'Pengajuan penarikan berhasil',
                'withdraw' => new WithdrawRequestResource($withdrawRequest),
            ], 201);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Riwayat withdraw request.
     */
    public function withdrawHistory(Request $request): AnonymousResourceCollection
    {
        $requests = $request->user()->withdrawRequests()->latest()->paginate(15);

        return WithdrawRequestResource::collection($requests);
    }
}
