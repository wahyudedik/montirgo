<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\PartnerSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    /**
     * Tampilkan halaman subscription partner.
     */
    public function index(Request $request): View
    {
        $partner = $request->user()->partner;

        $currentSubscription = PartnerSubscription::where('partner_id', $partner->id)
            ->where('status', 'active')
            ->first();

        $plans = [
            'basic' => [
                'name' => 'Basic',
                'price' => 0,
                'period' => 'Gratis',
                'features' => [
                    'Muncul di pencarian',
                    'Terima order standar',
                    '1 foto per order',
                    'Chat dengan customer',
                ],
                'color' => 'gray',
            ],
            'pro' => [
                'name' => 'Pro',
                'price' => 99000,
                'period' => '/bulan',
                'features' => [
                    'Semua fitur Basic',
                    'Prioritas dispatch',
                    '5 foto per order',
                    'Badge "Pro Partner"',
                    'Statistik detail',
                    'Sparepart marketplace',
                ],
                'color' => 'primary',
            ],
            'enterprise' => [
                'name' => 'Enterprise',
                'price' => 299000,
                'period' => '/bulan',
                'features' => [
                    'Semua fitur Pro',
                    'Top priority dispatch',
                    'Unlimited foto',
                    'Badge "Enterprise"',
                    'API access',
                    'Insurance partner',
                    'Dedicated support',
                    'Custom branding',
                ],
                'color' => 'amber',
            ],
        ];

        return view('partner.subscription.index', compact('currentSubscription', 'plans'));
    }

    /**
     * Subscribe ke plan baru.
     */
    public function subscribe(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan' => 'required|in:pro,enterprise',
        ]);

        $partner = $request->user()->partner;

        // Deactivate current subscription if any
        PartnerSubscription::where('partner_id', $partner->id)
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        $planPrice = $validated['plan'] === 'pro' ? 99000 : 299000;

        $subscription = PartnerSubscription::create([
            'partner_id' => $partner->id,
            'plan' => $validated['plan'],
            'amount' => $planPrice,
            'status' => 'active',
            'started_at' => now(),
            'expires_at' => now()->addMonth(),
            'features' => $this->getPlanFeatures($validated['plan']),
        ]);

        // Update partner subscription status
        $partner->update(['subscription_status' => $validated['plan']]);

        return redirect()
            ->route('partner.subscription.index')
            ->with('success', "Berhasil upgrade ke {$validated['plan']}! Berlaku sampai {$subscription->expires_at->format('d M Y')}.");
    }

    /**
     * Cancel subscription.
     */
    public function cancel(Request $request): RedirectResponse
    {
        $partner = $request->user()->partner;

        PartnerSubscription::where('partner_id', $partner->id)
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        $partner->update(['subscription_status' => 'basic']);

        return redirect()
            ->route('partner.subscription.index')
            ->with('success', 'Subscription berhasil dibatalkan. Anda kembali ke plan Basic.');
    }

    /**
     * Fitur-fitur per plan.
     */
    private function getPlanFeatures(string $plan): array
    {
        return match ($plan) {
            'pro' => [
                'prioritas_dispatch' => true,
                'foto_limit' => 5,
                'badge_pro' => true,
                'statistik_detail' => true,
                'sparepart_marketplace' => true,
            ],
            'enterprise' => [
                'prioritas_dispatch' => true,
                'foto_limit' => -1,
                'badge_enterprise' => true,
                'api_access' => true,
                'insurance_partner' => true,
                'dedicated_support' => true,
                'custom_branding' => true,
            ],
            default => [],
        };
    }
}
