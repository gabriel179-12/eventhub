<?php

use App\Models\Category;
use App\Models\Event;
use App\Models\Organizer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function publicEventForTicketListing(): Event
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
        'starts_at' => now()->addDays(10)->toISOString(),
        'ends_at' => now()->addDays(10)->addHours(8)->toISOString(),
        'venue_name' => 'Centro de Convenções',
        'address_line' => 'Rua Principal, 100',
        'city' => 'São Paulo',
        'state' => 'SP',
        'postal_code' => '01001-000',
        'capacity' => 500,
    ]);

    $event->status = 'published';
    $event->save();

    return $event->refresh();
}

test('a public event shows only ticket types currently available for sale', function (): void {
    $event = publicEventForTicketListing();

    $event->ticketTypes()->create([
        'name' => 'VIP',
        'price_in_cents' => 30000,
        'quantity' => 100,
    ]);
    $event->ticketTypes()->create([
        'name' => 'Inativo',
        'price_in_cents' => 10000,
        'quantity' => 100,
        'is_active' => false,
    ]);
    $event->ticketTypes()->create([
        'name' => 'Venda Futura',
        'price_in_cents' => 10000,
        'quantity' => 100,
        'sales_starts_at' => now()->addDay()->toISOString(),
    ]);
    $event->ticketTypes()->create([
        'name' => 'Venda Encerrada',
        'price_in_cents' => 10000,
        'quantity' => 100,
        'sales_starts_at' => now()->subDays(2)->toISOString(),
        'sales_ends_at' => now()->subHour()->toISOString(),
    ]);

    $this->getJson("/api/v1/events/{$event->slug}")
        ->assertOk()
        ->assertJsonCount(1, 'data.ticket_types')
        ->assertJsonPath('data.ticket_types.0.name', 'VIP')
        ->assertJsonPath('data.ticket_types.0.price_in_cents', 30000)
        ->assertJsonMissing(['name' => 'Inativo'])
        ->assertJsonMissing(['name' => 'Venda Futura'])
        ->assertJsonMissing(['name' => 'Venda Encerrada']);
});

test('a public event returns an empty ticket type list when none are available', function (): void {
    $event = publicEventForTicketListing();

    $this->getJson("/api/v1/events/{$event->slug}")
        ->assertOk()
        ->assertJsonCount(0, 'data.ticket_types');
});