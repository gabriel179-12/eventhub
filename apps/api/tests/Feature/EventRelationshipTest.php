<?php

use App\Models\Category;
use App\Models\Event;
use App\Models\Organizer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an event belongs to an organizer and a category', function (): void {
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
    ]);

    expect($event->organizer->is($organizer))->toBeTrue();
    expect($event->category->is($category))->toBeTrue();
    expect($organizer->events)->toHaveCount(1);
    expect($category->events)->toHaveCount(1);
});