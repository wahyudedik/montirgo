<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class VehicleService
{
    /**
     * Daftar kendaraan milik user.
     */
    public function listForUser(User $user)
    {
        return $user->vehicles()
            ->latest('is_default', 'desc')
            ->latest()
            ->get();
    }

    /**
     * Buat kendaraan baru.
     */
    public function create(User $user, array $data): Vehicle
    {
        return DB::transaction(function () use ($user, $data) {
            // Jika is_default = true, nonaktifkan default kendaraan lain
            if (($data['is_default'] ?? false) === true) {
                $user->vehicles()->where('is_default', true)->update(['is_default' => false]);
            }

            // Jika ini kendaraan pertama, otomatis jadikan default
            if ($user->vehicles()->count() === 0) {
                $data['is_default'] = true;
            }

            return $user->vehicles()->create($data);
        });
    }

    /**
     * Detail kendaraan (dengan authorization check).
     */
    public function getById(User $user, int $vehicleId): ?Vehicle
    {
        $vehicle = $user->vehicles()->where('id', $vehicleId)->first();

        return $vehicle;
    }

    /**
     * Perbarui kendaraan.
     */
    public function update(User $user, Vehicle $vehicle, array $data): Vehicle
    {
        return DB::transaction(function () use ($user, $vehicle, $data) {
            // Jika set default, nonaktifkan default lain
            if (($data['is_default'] ?? false) === true) {
                $user->vehicles()
                    ->where('id', '!=', $vehicle->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            $vehicle->update($data);

            return $vehicle->fresh();
        });
    }

    /**
     * Hapus kendaraan.
     */
    public function delete(User $user, Vehicle $vehicle): bool
    {
        return DB::transaction(function () use ($user, $vehicle) {
            $wasDefault = $vehicle->is_default;
            $vehicle->delete();

            // Jika yang dihapus adalah default, pindahkan default ke kendaraan lain
            if ($wasDefault) {
                $otherVehicle = $user->vehicles()->first();
                if ($otherVehicle) {
                    $otherVehicle->update(['is_default' => true]);
                }
            }

            return true;
        });
    }

    /**
     * Atur kendaraan sebagai default.
     */
    public function setDefault(User $user, Vehicle $vehicle): Vehicle
    {
        return DB::transaction(function () use ($user, $vehicle) {
            $user->vehicles()->where('is_default', true)->update(['is_default' => false]);
            $vehicle->update(['is_default' => true]);

            return $vehicle->fresh();
        });
    }

    /**
     * Cek apakah kendaraan sedang dipakai order aktif.
     */
    public function hasActiveOrder(Vehicle $vehicle): bool
    {
        return $vehicle->orders()
            ->whereIn('status', ['pending', 'dispatching', 'accepted', 'on_the_way', 'arrived', 'in_progress'])
            ->exists();
    }
}
