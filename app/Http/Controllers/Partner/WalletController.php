<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WalletController extends Controller
{
    /**
     * Halaman saldo partner.
     */
    public function index(): View
    {
        $user = Auth::user();
        $wallet = app(WalletService::class)->getBalance($user->id);
        $transactions = app(WalletService::class)->getTransactions($user->id, 20);
        $totalEarning = app(PaymentService::class)->getPartnerTotalEarning($user->partner->id);

        return view('partner.wallet.index', compact('wallet', 'transactions', 'totalEarning'));
    }

    /**
     * Form withdraw.
     */
    public function withdrawForm(): View
    {
        $user = Auth::user();
        $wallet = app(WalletService::class)->getBalance($user->id);

        return view('partner.wallet.withdraw', compact('wallet'));
    }

    /**
     * Proses withdraw request.
     */
    public function withdraw(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:10000|max:50000000',
            'bank_name' => 'required|string|max:100',
            'bank_account_number' => 'required|string|max:30',
            'bank_account_name' => 'required|string|max:100',
        ]);

        $user = Auth::user();

        try {
            app(WalletService::class)->requestWithdraw(
                $user->id,
                (float) $validated['amount'],
                $validated['bank_name'],
                $validated['bank_account_number'],
                $validated['bank_account_name'],
            );

            return redirect()->route('partner.wallet.index')
                ->with('success', 'Pengajuan penarikan sebesar Rp '.number_format($validated['amount'], 0, ',', '.').' berhasil diajukan.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Riwayat transaksi wallet.
     */
    public function history(): View
    {
        $user = Auth::user();
        $transactions = app(WalletService::class)->getTransactions($user->id, 50);

        return view('partner.wallet.history', compact('transactions'));
    }
}
