<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
        $captchaRule = config('recaptcha.secret_key') ? ['required', 'string'] : ['nullable', 'string'];

        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'captcha_token' => $captchaRule,
        ];
    }
}
