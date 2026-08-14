<?php

use App\Models\Organizer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('an owner can update an organizer', function (): void {
    $owner = User::factory()->create();

    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
        'description' => 'Descrição antiga.',
    ]);

    $owner->organizers()->attach($organizer->id, [
        'role' => 'owner',
    ]);

    Sanctum::actingAs($owner);

    $this->patchJson('/api/v1/organizers/eventhub-producoes', [
        'name' => 'EventHub Eventos',
        'description' => 'Nova descrição.',
        'social_links' => [
            'website' => 'https://eventhub.test',
        ],
    ])
        ->assertOk()
        ->assertJsonPath('data.name', 'EventHub Eventos')
        ->assertJsonPath('data.description', 'Nova descrição.')
        ->assertJsonPath(
            'data.social_links.website',
            'https://eventhub.test',
        );

    $organizer->refresh();

    expect($organizer->name)->toBe('EventHub Eventos');
    expect($organizer->slug)->toBe('eventhub-producoes');
});

test('a non-owner cannot update an organizer', function (): void {
    $manager = User::factory()->create();

    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);

    $manager->organizers()->attach($organizer->id, [
        'role' => 'manager',
    ]);

    Sanctum::actingAs($manager);

    $this->patchJson('/api/v1/organizers/eventhub-producoes', [
        'name' => 'Nome Indevido',
    ])->assertForbidden();
});

test('an organizer slug cannot be changed through update', function (): void {
    $owner = User::factory()->create();

    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);

    $owner->organizers()->attach($organizer->id, [
        'role' => 'owner',
    ]);

    Sanctum::actingAs($owner);

    $this->patchJson('/api/v1/organizers/eventhub-producoes', [
        'name' => 'EventHub Eventos',
        'slug' => 'novo-slug',
    ])->assertOk();

    expect($organizer->fresh()->slug)
        ->toBe('eventhub-producoes');
});