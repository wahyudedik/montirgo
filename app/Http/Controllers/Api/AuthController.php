<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Register user baru (customer atau partner).
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['sometimes', 'in:customer,partner'],
        ]);

        // Security: Block admin registration via public API — admin only via seeder/manual
        $requestedRole = $validated['role'] ?? 'customer';
        if ($requestedRole === 'admin') {
            return response()->json([
                'message' => 'Registrasi admin tidak diperbolehkan melalui publik API',
            ], 403);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role' => $requestedRole,
            'is_active' => true,
        ]);

        // Partner: create blank partner record for verification flow
        if ($requestedRole === 'partner') {
            $user->partner()->create([
                'workshop_name' => $validated['name'],
                'workshop_address' => '',
                'workshop_lat' => 0,
                'workshop_lng' => 0,
                'status' => 'pending',
            ]);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil',
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    /**
     * Login.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($validated)) {
            return response()->json([
                'message' => 'Email atau password salah',
            ], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->is_active) {
            return response()->json([
                'message' => 'Akun Anda telah dinonaktifkan',
            ], 403);
        }

        // Revoke previous tokens
        $user->tokens()->delete();

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    /**
     * Logout — revoke token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil',
        ]);
    }

    /**
     * Profil user yang sedang login.
     */
    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }

    /**
     * Update profil.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'location_lat' => ['sometimes', 'nullable', 'numeric'],
            'location_lng' => ['sometimes', 'nullable', 'numeric'],
        ]);

        $request->user()->update($validated);

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'user' => new UserResource($request->user()->fresh()),
        ]);
    }

    /**
     * Update lokasi partner (untuk tracking).
     */
    public function updateLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'location_lat' => ['required', 'numeric'],
            'location_lng' => ['required', 'numeric'],
        ]);

        $request->user()->update([
            'location_lat' => $validated['location_lat'],
            'location_lng' => $validated['location_lng'],
            'last_active_at' => now(),
        ]);

        return response()->json([
            'message' => 'Lokasi berhasil diperbarui',
        ]);
    }
}
