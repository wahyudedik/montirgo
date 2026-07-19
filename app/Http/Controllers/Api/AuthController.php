<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\UpdateLocationRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\CaptchaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register user baru (customer atau partner).
     */
    public function register(RegisterRequest $request, CaptchaService $captcha): JsonResponse
    {
        // Verify CAPTCHA token if configured
        $captchaToken = $request->input('captcha_token');
        if ($captcha->isActive() && $captchaToken) {
            $result = $captcha->verify($captchaToken, $request->ip());
            if (! $result['success']) {
                return response()->json([
                    'message' => 'Verifikasi CAPTCHA gagal. Silakan coba lagi.',
                ], 422);
            }
        }

        $validated = $request->validated();

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

        // Partner: create blank partner record — status 'draft' until profile completed
        if ($requestedRole === 'partner') {
            $user->partner()->create([
                'workshop_name' => $validated['workshop_name'] ?? $validated['name'],
                'workshop_address' => $validated['workshop_address'] ?? '',
                'workshop_lat' => $validated['workshop_lat'] ?? 0,
                'workshop_lng' => $validated['workshop_lng'] ?? 0,
                'workshop_category' => $validated['workshop_category'] ?? 'both',
                'service_radius' => $validated['service_radius'] ?? 30,
                'owner_name' => $validated['owner_name'] ?? null,
                'owner_phone' => $validated['owner_phone'] ?? null,
                'status' => 'draft',
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
    public function login(LoginRequest $request, CaptchaService $captcha): JsonResponse
    {
        // Verify CAPTCHA token if configured
        $captchaToken = $request->input('captcha_token');
        if ($captcha->isActive() && $captchaToken) {
            $result = $captcha->verify($captchaToken, $request->ip());
            if (! $result['success']) {
                return response()->json([
                    'message' => 'Verifikasi CAPTCHA gagal. Silakan coba lagi.',
                ], 422);
            }
        }

        $validated = $request->validated();

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
     * Logout — revoke token + clear FCM token.
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Clear all FCM tokens
        $user->removeAllFcmTokens();
        $user->update(['fcm_token' => null]);

        $user->currentAccessToken()->delete();

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
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $request->user()->update($validated);

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'user' => new UserResource($request->user()->fresh()),
        ]);
    }

    /**
     * Update lokasi partner (untuk tracking).
     */
    public function updateLocation(UpdateLocationRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $request->user()->update([
            'location_lat' => $validated['location_lat'],
            'location_lng' => $validated['location_lng'],
            'last_active_at' => now(),
        ]);

        return response()->json([
            'message' => 'Lokasi berhasil diperbarui',
        ]);
    }

    /**
     * Cek kelengkapan profil user yang sedang login.
     */
    public function profileCompletion(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user_completion' => $user->getProfileCompletionPercentage(),
            'is_profile_complete' => $user->isProfileComplete(),
        ]);
    }

    /**
     * Hapus akun user (soft delete) — user harus login.
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Clear all FCM tokens
        $user->removeAllFcmTokens();

        // Revoke all tokens
        $user->tokens()->delete();

        // Soft delete the user
        $user->delete();

        return response()->json([
            'message' => 'Akun berhasil dihapus',
        ]);
    }
}
