<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreateSOSRequest extends FormRequest
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
            'sos_type' => ['required', 'string', 'in:flat_tire,dead_battery,out_of_fuel,locked_keys,overheat'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'location_lat' => ['required', 'numeric'],
            'location_lng' => ['required', 'numeric'],
            'location_address' => ['nullable', 'string'],
        ];
    }
}
