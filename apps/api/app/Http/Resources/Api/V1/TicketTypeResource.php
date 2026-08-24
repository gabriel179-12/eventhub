<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return[
            'id' => $this->id,
            'event_id' => $this->event_id,
            'name' => $this->name,
            'description' => $this->description,
            'price_in_cents' => $this->price_in_cents,
            'quantity' => $this->quantity,
            'quantity_sold' => $this->quantity_sold,
            'sales_starts_at' => $this->sales_starts_at?->toISOString(),
            'sales_ends_at' => $this->sales_ends_at?->toISOString(),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}