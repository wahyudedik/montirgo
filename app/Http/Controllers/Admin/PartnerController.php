<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartnerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Partner::with('user');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('workshop_name', 'like', "%{$search}%")
                    ->orWhere('workshop_address', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $partners = $query->latest()->paginate(15);

        return view('admin.partners.index', compact('partners'));
    }

    public function show(Partner $partner): View
    {
        $partner->load(['user', 'services', 'reviews' => function ($q) {
            $q->latest()->take(5);
        }]);

        return view('admin.partners.show', compact('partner'));
    }

    public function approve(Partner $partner): RedirectResponse
    {
        $partner->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', "Partner {$partner->workshop_name} approved successfully.");
    }

    public function reject(Request $request, Partner $partner): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $partner->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return redirect()->back()
            ->with('success', "Partner {$partner->workshop_name} rejected.");
    }

    public function suspend(Partner $partner): RedirectResponse
    {
        $partner->update(['status' => 'suspended']);

        return redirect()->back()
            ->with('success', "Partner {$partner->workshop_name} suspended.");
    }

    public function destroy(Partner $partner): RedirectResponse
    {
        $partner->delete();

        return redirect()->route('admin.partners.index')
            ->with('success', "Partner {$partner->workshop_name} deleted.");
    }
}
