<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'workshop_name' => $this->workshop_name,
            'owner_name' => $this->owner_name,
            'owner_phone' => $this->owner_phone,
            'workshop_address' => $this->workshop_address,
            'workshop_lat' => $this->workshop_lat,
            'workshop_lng' => $this->workshop_lng,
            'workshop_category' => $this->workshop_category,
            'service_radius' => $this->service_radius,
            'status' => $this->status,
            'partner_status' => $this->partner_status,
            'is_online' => $this->is_online,
            'is_available' => $this->is_available,
            'rating' => $this->rating_avg,
            'total_orders' => $this->total_orders,
            'total_reviews' => $this->total_reviews,
            'description' => $this->description,
            'operating_hours' => $this->operating_hours,
            'operational_schedule' => $this->operational_schedule,
            'is_operating' => $this->isCurrentlyOperating(),
            'last_active_at' => $this->last_active_at?->toIso8601String(),
            'profile_completion' => $this->getProfileCompletionPercentage(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
