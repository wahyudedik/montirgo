<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
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
        return [
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'service_type' => ['required', 'string', 'max:255'],
            'problem_description' => ['nullable', 'string'],
            'location_lat' => ['required', 'numeric'],
            'location_lng' => ['required', 'numeric'],
            'location_address' => ['nullable', 'string'],
            'payment_method' => ['required', 'in:qris,ewallet,bank_transfer'],
        ];
    }
}
