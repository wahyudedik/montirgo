<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'status_color' => $this->status_color,
            'service_type' => $this->service_type,
            'problem_description' => $this->problem_description,
            'location' => [
                'lat' => $this->location_lat,
                'lng' => $this->location_lng,
                'address' => $this->location_address,
            ],
            'fees' => [
                'callout_fee' => (float) $this->callout_fee,
                'service_fee' => (float) $this->service_fee,
                'total_amount' => (float) $this->total_amount,
                'platform_commission' => (float) $this->platform_commission,
                'partner_earning' => (float) $this->partner_earning,
                'total_display' => $this->total_display,
            ],
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'cancel_reason' => $this->cancel_reason,
            'cancelled_by' => $this->cancelled_by,
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'dispatch_started_at' => $this->dispatch_started_at?->toIso8601String(),
            'user' => new UserResource($this->whenLoaded('user')),
            'partner' => new PartnerResource($this->whenLoaded('partner')),
            'vehicle' => new VehicleResource($this->whenLoaded('vehicle')),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
