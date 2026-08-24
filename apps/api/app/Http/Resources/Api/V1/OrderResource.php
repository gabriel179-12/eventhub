<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status,
            'total_in_cents' => $this->total_in_cents,
            'expires_at' => $this->expires_at?->toISOString(),
            'paid_at' => $this->paid_at?->toISOString(),
            'items' => $this->whenLoaded('items', function (): array {
                return $this->items->map(function ($item): array {
                    return [
                        'id' => $item->id,
                        'ticket_type_id' => $item->ticket_type_id,
                        'ticket_type_name' => $item->ticket_type_name,
                        'unit_price_in_cents' => $item->unit_price_in_cents,
                        'quantity' => $item->quantity,
                        'total_in_cents' => $item->total_in_cents,
                    ];
                })->all();
            }),
        ];
    }
}