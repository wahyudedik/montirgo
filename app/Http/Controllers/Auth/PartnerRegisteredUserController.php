<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PartnerRegisteredUserController extends Controller
{
    /**
     * Display the partner registration view.
     */
    public function create(): View
    {
        return view('auth.register-partner');
    }

    /**
     * Handle an incoming partner registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['required', 'string', 'max:20'],
            'workshop_name' => ['required', 'string', 'max:255'],
            'workshop_address' => ['required', 'string', 'max:500'],
        ]);

        // Security: Web partner registration always creates partner role
        // Admin role is NEVER allowed via public registration
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'],
            'role' => 'partner',
            'is_active' => true,
        ]);

        // Create partner record with pending status
        $user->partner()->create([
            'workshop_name' => $validated['workshop_name'],
            'workshop_address' => $validated['workshop_address'],
            'workshop_lat' => 0,
            'workshop_lng' => 0,
            'status' => 'pending',
        ]);

        // Create wallet for partner
        $user->walletBalance()->create([
            'balance' => 0,
            'total_income' => 0,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('partner.pending')
            ->with('status', 'Akun partner Anda berhasil dibuat. Menunggu verifikasi dari admin MontirGo.');
    }
}
