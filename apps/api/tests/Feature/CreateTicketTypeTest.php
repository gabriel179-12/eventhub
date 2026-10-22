<?php

use App\Models\Category;
use App\Models\Event;
use App\Models\Organizer;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function eventForTicketTypeCreation(Organizer $organizer): Event
{
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

function ticketTypePayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'VIP',
        'description' => 'Área exclusiva.',
        'price_in_cents' => 30000,
        'quantity' => 100,
        'is_active' => true,
    ], $overrides);
}

test('an owner or manager can create a ticket type', function (string $teamRole): void {
    $user = User::factory()->create();
    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);
    $event = eventForTicketTypeCreation($organizer);

    $organizer->users()->attach($user->id, ['role' => $teamRole]);

    Sanctum::actingAs($user);

    $this->postJson(
        "/api/v1/organizers/{$organizer->slug}/events/{$event->slug}/ticket-types",
        ticketTypePayload(),
    )
        ->assertCreated()
        ->assertJsonPath('data.name', 'VIP')
        ->assertJsonPath('data.price_in_cents', 30000)
        ->assertJsonPath('data.quantity', 100)
        ->assertJsonPath('data.quantity_sold', 0);

    $ticketType = TicketType::query()
        ->where('event_id', $event->id)
        ->where('name', 'VIP')
        ->firstOrFail();

    expect($ticketType->quantity_sold)->toBe(0);
})->with(['owner', 'manager']);

test('checkin staff cannot create a ticket type', function (): void {
    $user = User::factory()->create();
    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);
    $event = eventForTicketTypeCreation($organizer);

    $organizer->users()->attach($user->id, ['role' => 'checkin_staff']);

    Sanctum::actingAs($user);

    $this->postJson(
        "/api/v1/organizers/{$organizer->slug}/events/{$event->slug}/ticket-types",
        ticketTypePayload(),
    )->assertForbidden();
});

test('a ticket type price cannot be negative', function (): void {
    $user = User::factory()->create();
    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);
    $event = eventForTicketTypeCreation($organizer);

    $organizer->users()->attach($user->id, ['role' => 'owner']);

    Sanctum::actingAs($user);

    $this->postJson(
        "/api/v1/organizers/{$organizer->slug}/events/{$event->slug}/ticket-types",
        ticketTypePayload(['price_in_cents' => -1]),
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['price_in_cents']);
});

test('a ticket type quantity must be positive', function (): void {
    $user = User::factory()->create();
    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);
    $event = eventForTicketTypeCreation($organizer);

    $organizer->users()->attach($user->id, ['role' => 'owner']);

    Sanctum::actingAs($user);

    $this->postJson(
        "/api/v1/organizers/{$organizer->slug}/events/{$event->slug}/ticket-types",
        ticketTypePayload(['quantity' => 0]),
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['quantity']);
});

test('an event cannot have ticket types with the same name', function (): void {
    $user = User::factory()->create();
    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);
    $event = eventForTicketTypeCreation($organizer);

    $organizer->users()->attach($user->id, ['role' => 'owner']);
    $event->ticketTypes()->create(ticketTypePayload());

    Sanctum::actingAs($user);

    $this->postJson(
        "/api/v1/organizers/{$organizer->slug}/events/{$event->slug}/ticket-types",
        ticketTypePayload(),
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('an event from another organizer cannot receive a ticket type through this organizer', function (): void {
    $user = User::factory()->create();
    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);
    $otherOrganizer = Organizer::query()->create([
        'name' => 'Outra Produção',
        'slug' => 'outra-producao',
    ]);
    $event = eventForTicketTypeCreation($otherOrganizer);

    $organizer->users()->attach($user->id, ['role' => 'owner']);

    Sanctum::actingAs($user);

    $this->postJson(
        "/api/v1/organizers/{$organizer->slug}/events/{$event->slug}/ticket-types",
        ticketTypePayload(),
    )->assertNotFound();
});

test('an event cannot receive a second ticket type', function (string $role): void {
    $user = User::factory()->create();

    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);

    $event = eventForTicketTypeCreation($organizer);

    $organizer->users()->attach($user->id, [
        'role' => $role,
    ]);

    $originalTicket = $event->ticketTypes()->create([
        'name' => 'Ingresso',
        'price_in_cents' => 8000,
        'quantity' => 500,
    ]);

    Sanctum::actingAs($user);

    $this->postJson(
        "/api/v1/organizers/{$organizer->slug}/events/{$event->slug}/ticket-types",
        ticketTypePayload([
            'name' => 'Outro ingresso',
            'price_in_cents' => 12000,
        ]),
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['event']);

    expect($event->ticketTypes()->count())->toBe(1);
    expect($originalTicket->fresh()->price_in_cents)->toBe(8000);
})->with(['owner', 'manager']);