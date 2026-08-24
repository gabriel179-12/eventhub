<?php

namespace App\Domains\Tickets\Actions;

use App\Models\Event;
use App\Models\TicketType;

final class CreateTicketType
{
    public function handle(Event $event, array $attributes): TicketType{
        $ticketType = $event->ticketTypes()->create([
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
            'price_in_cents' => $attributes['price_in_cents'],
            'quantity' => $attributes['quantity'],
            'sales_starts_at' => $attributes['sales_starts_at'] ?? null,
            'sales_ends_at' => $attributes['sales_ends_at'] ?? null,
            'is_active' => $attributes['is_active'] ?? true,
        ]);

        return $ticketType->refresh();
    }
}