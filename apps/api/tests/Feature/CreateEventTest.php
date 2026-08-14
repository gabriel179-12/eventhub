<?php

use App\Models\Category;
use App\Models\Event;
use App\Models\Organizer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function validEventPayload(int $categoryId, array $overrides = []): array
{
    return array_merge([
        'category_id' => $categoryId,
        'name' => 'Laravel Conference',
        'slug' => 'laravel-conference-2026',
        'description' => 'Evento para a comunidade Laravel.',
        'starts_at' => '2026-12-10T19:00:00-03:00',
        'ends_at' => '2026-12-10T22:00:00-03:00',
        'venue_name' => 'Centro de Eventos',
        'address_line' => 'Rua Principal, 100',
        'city' => 'São Paulo',
        'state' => 'SP',
        'postal_code' => '01000-000',
        'capacity' => 500,
        'is_private' => false,
    ], $overrides);
}

test('an owner or manager can create an event', function (string $membershipRole): void {
    $user = User::factory()->create();

    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);

    $category = Category::query()->create([
        'name' => 'Tecnologia',
        'slug' => 'tecnologia',
    ]);

    $user->organizers()->attach($organizer->id, [
        'role' => $membershipRole,
    ]);

    Sanctum::actingAs($user);

    $this->postJson(
        '/api/v1/organizers/eventhub-producoes/events',
        validEventPayload($category->id),
    )
        ->assertCreated()
        ->assertJsonPath('data.slug', 'laravel-conference-2026')
        ->assertJsonPath('data.status', 'draft');

    $event = Event::query()
        ->where('slug', 'laravel-conference-2026')
        ->firstOrFail();

    expect($event->organizer_id)->toBe($organizer->id);
    expect($event->category_id)->toBe($category->id);
    expect($event->status)->toBe('draft');
})->with(['owner', 'manager']);

test('checkin staff cannot create an event', function (): void {
    $staff = User::factory()->create();

    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);

    $category = Category::query()->create([
        'name' => 'Tecnologia',
        'slug' => 'tecnologia',
    ]);

    $staff->organizers()->attach($organizer->id, [
        'role' => 'checkin_staff',
    ]);

    Sanctum::actingAs($staff);

    $this->postJson(
        '/api/v1/organizers/eventhub-producoes/events',
        validEventPayload($category->id),
    )->assertForbidden();
});

test('an event must end after it starts', function (): void {
    $owner = User::factory()->create();

    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);

    $category = Category::query()->create([
        'name' => 'Tecnologia',
        'slug' => 'tecnologia',
    ]);

    $owner->organizers()->attach($organizer->id, [
        'role' => 'owner',
    ]);

    Sanctum::actingAs($owner);

    $this->postJson(
        '/api/v1/organizers/eventhub-producoes/events',
        validEventPayload($category->id, [
            'ends_at' => '2026-12-10T18:00:00-03:00',
        ]),
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['ends_at']);
});

test('an event slug must be unique', function (): void {
    $owner = User::factory()->create();

    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);

    $category = Category::query()->create([
        'name' => 'Tecnologia',
        'slug' => 'tecnologia',
    ]);

    $organizer->events()->create(validEventPayload($category->id));

    $owner->organizers()->attach($organizer->id, [
        'role' => 'owner',
    ]);

    Sanctum::actingAs($owner);

    $this->postJson(
        '/api/v1/organizers/eventhub-producoes/events',
        validEventPayload($category->id, [
            'name' => 'Outro Evento',
        ]),
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['slug']);
});