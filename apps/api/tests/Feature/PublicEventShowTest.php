<?php

use App\Models\Category;
use App\Models\Event;
use App\Models\Organizer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createPublicEventForShow(
    Organizer $organizer,
    Category $category,
    array $overrides = [],
): Event {
    $attributes = array_merge([
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
        'is_private' => false,
        'status' => 'published',
    ], $overrides);

    $status = $attributes['status'];
    unset($attributes['status']);

    $event = $organizer->events()->create($attributes);

    $event->status = $status;
    $event->save();

    return $event->refresh();
}

test('a visitor can view a published public upcoming event', function (): void {
    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);
    $category = Category::query()->create([
        'name' => 'Tecnologia',
        'slug' => 'tecnologia',
    ]);
    $event = createPublicEventForShow($organizer, $category);

    $this->getJson("/api/v1/events/{$event->slug}")
        ->assertOk()
        ->assertJsonPath('data.slug', 'laravel-conference-2026')
        ->assertJsonPath('data.status', 'published')
        ->assertJsonPath('data.organizer.name', 'EventHub Produções')
        ->assertJsonPath('data.category.slug', 'tecnologia');
});

test('a visitor cannot view a draft event', function (): void {
    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);
    $category = Category::query()->create([
        'name' => 'Tecnologia',
        'slug' => 'tecnologia',
    ]);
    $event = createPublicEventForShow($organizer, $category, [
        'status' => 'draft',
    ]);

    $this->getJson("/api/v1/events/{$event->slug}")
        ->assertNotFound();
});

test('a visitor cannot view a private event', function (): void {
    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);
    $category = Category::query()->create([
        'name' => 'Tecnologia',
        'slug' => 'tecnologia',
    ]);
    $event = createPublicEventForShow($organizer, $category, [
        'is_private' => true,
    ]);

    $this->getJson("/api/v1/events/{$event->slug}")
        ->assertNotFound();
});

test('a visitor cannot view a past event', function (): void {
    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);
    $category = Category::query()->create([
        'name' => 'Tecnologia',
        'slug' => 'tecnologia',
    ]);
    $event = createPublicEventForShow($organizer, $category, [
        'starts_at' => now()->subDays(2)->toISOString(),
        'ends_at' => now()->subDays(2)->addHours(8)->toISOString(),
    ]);

    $this->getJson("/api/v1/events/{$event->slug}")
        ->assertNotFound();
});