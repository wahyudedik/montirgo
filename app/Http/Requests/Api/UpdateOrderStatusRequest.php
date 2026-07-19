<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
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
            'status' => ['required', 'in:on_the_way,arrived,in_progress,completed'],
            'service_fee' => ['required_if:status,completed', 'nullable', 'numeric', 'min:0'],
        ];
    }
}
