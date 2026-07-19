<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    /**
     * Storage disk yang digunakan (default dari env STORAGE_DISK).
     */
    private string $disk;

    public function __construct(?string $disk = null)
    {
        $this->disk = $disk ?? config('filesystems.default', 'public');
    }

    /**
     * Upload file ke storage.
     *
     * @param  string  $directory  Folder tujuan (e.g. 'order-photos', 'spareparts')
     * @param  string|null  $name  Nama file custom (null = random name)
     * @return string Path file yang tersimpan
     */
    public function upload(UploadedFile $file, string $directory, ?string $name = null): string
    {
        return $file->store($directory, [
            'disk' => $this->disk,
            'name' => $name,
        ]);
    }

    /**
     * Upload file dengan validasi ukuran dan tipe.
     *
     * @param  array{max_size?: int, allowed_types?: array<string>}  $options
     * @return string Path file yang tersimpan
     *
     * @throws \InvalidArgumentException
     */
    public function uploadValidated(
        UploadedFile $file,
        string $directory,
        array $options = [],
    ): string {
        $maxSize = $options['max_size'] ?? 5120; // 5MB default
        $allowedTypes = $options['allowed_types'] ?? ['jpg', 'jpeg', 'png', 'webp'];

        if ($file->getSize() > $maxSize * 1024) {
            throw new \InvalidArgumentException("Ukuran file melebihi batas maksimum {$maxSize}KB.");
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, $allowedTypes)) {
            throw new \InvalidArgumentException("Tipe file '{$extension}' tidak diizinkan. Yang diperbolehkan: ".implode(', ', $allowedTypes));
        }

        return $this->upload($file, $directory);
    }

    /**
     * Hapus file dari storage.
     */
    public function delete(string $path): bool
    {
        if ($path && Storage::disk($this->disk)->exists($path)) {
            return Storage::disk($this->disk)->delete($path);
        }

        return false;
    }

    /**
     * Generate URL untuk mengakses file.
     */
    public function url(string $path): string
    {
        return Storage::disk($this->disk)->url($path);
    }

    /**
     * Ganti file lama dengan file baru (hapus lama, upload baru).
     *
     * @return string Path file baru
     */
    public function replace(?string $oldPath, UploadedFile $newFile, string $directory): string
    {
        if ($oldPath) {
            $this->delete($oldPath);
        }

        return $this->upload($newFile, $directory);
    }

    /**
     * Dapatkan nama disk yang aktif.
     */
    public function getDisk(): string
    {
        return $this->disk;
    }
}
