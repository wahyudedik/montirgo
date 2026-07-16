<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WithdrawRequest;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WithdrawController extends Controller
{
    /**
     * Daftar semua withdraw request.
     */
    public function index(Request $request): View
    {
        $query = WithdrawRequest::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $requests = $query->paginate(15);

        $stats = [
            'pending' => WithdrawRequest::where('status', 'pending')->count(),
            'approved' => WithdrawRequest::where('status', 'approved')->count(),
            'rejected' => WithdrawRequest::where('status', 'rejected')->count(),
            'total_pending_amount' => (float) WithdrawRequest::where('status', 'pending')->sum('amount'),
        ];

        return view('admin.withdraws.index', compact('requests', 'stats'));
    }

    /**
     * Detail withdraw request.
     */
    public function show(WithdrawRequest $request): View
    {
        $request->load('user');

        return view('admin.withdraws.show', ['withdrawRequest' => $request]);
    }

    /**
     * Setujui withdraw request.
     */
    public function approve(WithdrawRequest $withdrawRequest): RedirectResponse
    {
        if ($withdrawRequest->status !== 'pending') {
            return back()->with('error', 'Withdraw request ini sudah diproses.');
        }

        try {
            app(WalletService::class)->approveWithdraw($withdrawRequest);

            return redirect()->route('admin.withdraws.show', $withdrawRequest)
                ->with('success', 'Withdraw request berhasil disetujui.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Tolak withdraw request.
     */
    public function reject(Request $request, WithdrawRequest $withdrawRequest): RedirectResponse
    {
        if ($withdrawRequest->status !== 'pending') {
            return back()->with('error', 'Withdraw request ini sudah diproses.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        try {
            app(WalletService::class)->rejectWithdraw($withdrawRequest, $validated['rejection_reason']);

            return redirect()->route('admin.withdraws.show', $withdrawRequest)
                ->with('success', 'Withdraw request ditolak.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
