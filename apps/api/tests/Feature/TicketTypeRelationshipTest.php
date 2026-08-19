<?php

use App\Models\Category;
use App\Models\Event;
use App\Models\Organizer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function eventForTicketTypeRelationship(): Event
{
    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);
    $category = Category::query()->create([
        'name' => 'Tecnologia',
        'slug' => 'tecnologia',
    ]);

    return $organizer->events()->create([
        'category_id' => $category->id,
        'name' => 'Laravel Conference',
        'slug' => 'laravel-conference-2026',
        'description' => 'Evento sobre Laravel.',
        'starts_at' => now()->addDays(10)->toISOString(),
        'ends_at' => now()->addDays(10)->addHours(8)->toISOString(),
        'venue_name' => 'Centro de Convenções',
        'address_line' => 'Rua Principal, 100',
        'city' => 'São Paulo',
        'state' => 'SP',
        'postal_code' => '01001-000',
        'capacity' => 500,
    ]);
}

test('an event can have multiple ticket types', function (): void {
    $event = eventForTicketTypeRelationship();

    $event->ticketTypes()->create([
        'name' => 'VIP',
        'price_in_cents' => 30000,
        'quantity' => 100,
    ]);
    $event->ticketTypes()->create([
        'name' => 'Pista',
        'price_in_cents' => 10000,
        'quantity' => 1000,
    ]);

    expect(
        $event->ticketTypes()
            ->orderBy('name')
            ->pluck('name')
            ->all(),
    )->toBe(['Pista', 'VIP']);
});

test('a ticket type belongs to an event', function (): void {
    $event = eventForTicketTypeRelationship();

    $ticketType = $event->ticketTypes()->create([
        'name' => 'VIP',
        'price_in_cents' => 30000,
        'quantity' => 100,
    ]);

    expect($ticketType->event->is($event))->toBeTrue();
    expect($ticketType->fresh()->quantity_sold)->toBe(0);
});