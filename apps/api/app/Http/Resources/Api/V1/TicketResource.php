<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
   /**
     * @return array<string, mixed>
     */

    public function toArray(Request $request): array
    {
        return[
            'code' => $this->code,
            'status' => $this->status,
            'checked_in_at' => $this->checked_in_at?->toISOString(),
        ];
    }
}
