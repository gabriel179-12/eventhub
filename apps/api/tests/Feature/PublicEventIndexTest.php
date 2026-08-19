<?php

use App\Models\Category;
use App\Models\Event;
use App\Models\Organizer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createCatalogEvent(
    Organizer $organizer,
    Category $category,
    array $overrides = [],
): Event {
    $attributes = array_merge([
        'category_id' => $category->id,
        'name' => 'Evento de Tecnologia',
        'slug' => 'evento-tecnologia',
        'description' => 'Descrição do evento.',
        'starts_at' => now()->addDays(10)->toISOString(),
        'ends_at' => now()->addDays(10)->addHours(8)->toISOString(),
        'venue_name' => 'Centro de Convenções',
        'address_line' => 'Rua Principal, 100',
        'city' => 'São Paulo',
        'state' => 'SP',
        'postal_code' => '01001-000',
        'capacity' => 500,
        'is_private' => false,
        'status' => 'draft',
    ], $overrides);

    $status = $attributes['status'];
    unset($attributes['status']);

    $event = $organizer->events()->create($attributes);

    $event->status = $status;
    $event->save();

    return $event->refresh();
}

test('the public catalog lists only upcoming published public events ordered by date', function (): void {
    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);
    $category = Category::query()->create([
        'name' => 'Tecnologia',
        'slug' => 'tecnologia',
    ]);

    createCatalogEvent($organizer, $category, [
        'name' => 'Evento Posterior',
        'slug' => 'evento-posterior',
        'starts_at' => now()->addDays(30)->toISOString(),
        'ends_at' => now()->addDays(30)->addHours(8)->toISOString(),
        'status' => 'published',
    ]);

    createCatalogEvent($organizer, $category, [
        'name' => 'Próximo Evento',
        'slug' => 'proximo-evento',
        'starts_at' => now()->addDays(7)->toISOString(),
        'ends_at' => now()->addDays(7)->addHours(8)->toISOString(),
        'status' => 'published',
    ]);

    createCatalogEvent($organizer, $category, [
        'slug' => 'evento-rascunho',
        'status' => 'draft',
    ]);

    createCatalogEvent($organizer, $category, [
        'slug' => 'evento-privado',
        'status' => 'published',
        'is_private' => true,
    ]);

    createCatalogEvent($organizer, $category, [
        'slug' => 'evento-passado',
        'starts_at' => now()->subDays(2)->toISOString(),
        'ends_at' => now()->subDays(2)->addHours(8)->toISOString(),
        'status' => 'published',
    ]);

    $this->getJson('/api/v1/events')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.slug', 'proximo-evento')
        ->assertJsonPath('data.1.slug', 'evento-posterior')
        ->assertJsonMissing(['slug' => 'evento-rascunho'])
        ->assertJsonMissing(['slug' => 'evento-privado'])
        ->assertJsonMissing(['slug' => 'evento-passado']);
});

test('a visitor can access an empty public catalog', function (): void {
    $this->getJson('/api/v1/events')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});