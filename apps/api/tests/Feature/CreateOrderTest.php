<?php

use App\Models\Category;
use App\Models\Event;
use App\Models\Organizer;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function eventForOrderCreation(): Event
{
    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);

    $category = Category::query()->create([
        'name' => 'Tecnologia',
        'slug' => 'tecnologia',
    ]);

    $event = $organizer->events()->create([
        'category_id' => $category->id,
        'name' => 'Laravel Conference',
        'slug' => 'laravel-conference-2026',
        'description' => 'Evento sobre Laravel.',
        'starts_at' => now()->addDays(10),
        'ends_at' => now()->addDays(10)->addHours(8),
        'venue_name' => 'Centro de Convenções',
        'address_line' => 'Rua Principal, 100',
        'city' => 'São Paulo',
        'state' => 'SP',
        'postal_code' => '01001-000',
        'capacity' => 500,
    ]);

    $event->status = 'published';
    $event->save();

    return $event;
}

function orderPayload(TicketType $ticketType, int $quantity = 2): array
{
    return [
        'items' => [
            [
                'ticket_type_id' => $ticketType->id,
                'quantity' => $quantity,
            ],
        ],
    ];
}

test('an authenticated user can create a pending order and reserve tickets', function (): void {
    $user = User::factory()->create();
    $event = eventForOrderCreation();

    $ticketType = $event->ticketTypes()->create([
        'name' => 'VIP',
        'price_in_cents' => 30000,
        'quantity' => 100,
        'is_active' => true,
    ]);

    Sanctum::actingAs($user);

    $this->postJson(
        "/api/v1/events/{$event->slug}/orders",
        orderPayload($ticketType),
    )
        ->assertCreated()
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.total_in_cents', 60000)
        ->assertJsonPath('data.items.0.ticket_type_name', 'VIP')
        ->assertJsonPath('data.items.0.unit_price_in_cents', 30000)
        ->assertJsonPath('data.items.0.quantity', 2);

    expect($ticketType->fresh()->quantity_reserved)->toBe(2);
});

test('a visitor cannot create an order', function (): void {
    $event = eventForOrderCreation();

    $ticketType = $event->ticketTypes()->create([
        'name' => 'VIP',
        'price_in_cents' => 30000,
        'quantity' => 100,
    ]);

    $this->postJson(
        "/api/v1/events/{$event->slug}/orders",
        orderPayload($ticketType),
    )->assertUnauthorized();
});

test('an order cannot reserve more tickets than are available', function (): void {
    $user = User::factory()->create();
    $event = eventForOrderCreation();

    $ticketType = $event->ticketTypes()->create([
        'name' => 'VIP',
        'price_in_cents' => 30000,
        'quantity' => 2,
    ]);

    $ticketType->quantity_sold = 1;
    $ticketType->save();

    Sanctum::actingAs($user);

    $this->postJson(
        "/api/v1/events/{$event->slug}/orders",
        orderPayload($ticketType, 2),
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['items.0.quantity']);

    expect($ticketType->fresh()->quantity_reserved)->toBe(0);
});