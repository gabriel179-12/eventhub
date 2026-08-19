<?php

use App\Models\Category;
use App\Models\Event;
use App\Models\Organizer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function eventForOrganizer(Organizer $organizer, Category $category): Event
{
    return $organizer->events()->create([
        'category_id' => $category->id,
        'name' => 'Laravel Conference',
        'slug' => 'laravel-conference-2026',
        'description' => 'Evento sobre Laravel.',
        'starts_at' => '2026-12-10T09:00:00-03:00',
        'ends_at' => '2026-12-10T18:00:00-03:00',
        'venue_name' => 'Centro de Convenções',
        'address_line' => 'Rua Principal, 100',
        'city' => 'São Paulo',
        'state' => 'SP',
        'postal_code' => '01001-000',
        'capacity' => 500,
    ]);
}

test('an owner or manager can publish a draft event', function (string $teamRole): void {
    $user = User::factory()->create();
    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);
    $category = Category::query()->create([
        'name' => 'Tecnologia',
        'slug' => 'tecnologia',
    ]);

    $organizer->users()->attach($user->id, ['role' => $teamRole]);

    $event = eventForOrganizer($organizer, $category);

    Sanctum::actingAs($user);

    $this->patchJson(
        "/api/v1/organizers/{$organizer->slug}/events/{$event->slug}/publish",
    )
        ->assertOk()
        ->assertJsonPath('data.status', 'published');

    expect($event->fresh()->status)->toBe('published');
})->with(['owner', 'manager']);

test('checkin staff cannot publish an event', function (): void {
    $user = User::factory()->create();
    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);
    $category = Category::query()->create([
        'name' => 'Tecnologia',
        'slug' => 'tecnologia',
    ]);

    $organizer->users()->attach($user->id, ['role' => 'checkin_staff']);

    $event = eventForOrganizer($organizer, $category);

    Sanctum::actingAs($user);

    $this->patchJson(
        "/api/v1/organizers/{$organizer->slug}/events/{$event->slug}/publish",
    )->assertForbidden();
});

test('an event from another organizer cannot be published through this organizer', function (): void {
    $user = User::factory()->create();
    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);
    $otherOrganizer = Organizer::query()->create([
        'name' => 'Outra Produção',
        'slug' => 'outra-producao',
    ]);
    $category = Category::query()->create([
        'name' => 'Tecnologia',
        'slug' => 'tecnologia',
    ]);

    $organizer->users()->attach($user->id, ['role' => 'owner']);

    $event = eventForOrganizer($otherOrganizer, $category);

    Sanctum::actingAs($user);

    $this->patchJson(
        "/api/v1/organizers/{$organizer->slug}/events/{$event->slug}/publish",
    )->assertNotFound();
});