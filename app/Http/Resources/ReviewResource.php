<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'partner_reply' => $this->partner_reply,
            'replied_at' => $this->replied_at?->toIso8601String(),
            'order' => [
                'id' => $this->whenLoaded('order'),
                'code' => $this->order?->code,
                'service_type' => $this->order?->service_type,
            ],
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'avatar' => $this->user?->avatar,
            ],
            'partner' => [
                'id' => $this->partner?->id,
                'workshop_name' => $this->partner?->workshop_name,
            ],
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
