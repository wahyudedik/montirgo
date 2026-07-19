<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePartnerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'workshop_name' => ['sometimes', 'string', 'max:255'],
            'workshop_address' => ['sometimes', 'nullable', 'string'],
            'workshop_lat' => ['sometimes', 'nullable', 'numeric'],
            'workshop_lng' => ['sometimes', 'nullable', 'numeric'],
            'workshop_category' => ['sometimes', 'nullable', 'string', 'in:motorcycle,car,both'],
            'service_radius' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100'],
            'owner_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'owner_phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'description' => ['sometimes', 'nullable', 'string'],
            'operating_hours' => ['sometimes', 'nullable', 'string'],
            'operational_schedule' => ['sometimes', 'nullable', 'array'],
            'operational_schedule.*.open' => ['required_with:operational_schedule', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'operational_schedule.*.close' => ['required_with:operational_schedule', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'ktp_number' => ['sometimes', 'nullable', 'string', 'max:50'],
            'bank_name' => ['sometimes', 'nullable', 'string', 'max:100'],
            'bank_account_number' => ['sometimes', 'nullable', 'string', 'max:50'],
            'bank_account_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'npwp' => ['sometimes', 'nullable', 'string', 'max:50'],
            'nib' => ['sometimes', 'nullable', 'string', 'max:50'],
        ];

        // File uploads — image validation
        $imageRule = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']; // 5MB max
        $rules['ktp_photo'] = $imageRule;
        $rules['selfie_with_ktp'] = $imageRule;
        $rules['workshop_photo'] = $imageRule;
        $rules['front_workshop_photo'] = $imageRule;
        $rules['inside_workshop_photo'] = $imageRule;
        $rules['business_license'] = $imageRule;

        return $rules;
    }
}
