<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'balance' => (float) $this->balance,
            'frozen' => (float) $this->frozen,
            'total_income' => (float) $this->total_income,
            'total_withdrawn' => (float) $this->total_withdrawn,
            'balance_display' => 'Rp '.number_format((float) $this->balance, 0, ',', '.'),
        ];
    }
}
