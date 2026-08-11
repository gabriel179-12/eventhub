<?php

use App\Models\Organizer;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('an authenticated user can create an organizer and becomes its owner', function (): void {
    $this->seed(RoleSeeder::class);

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/organizers', [
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
        'description' => 'Eventos de tecnologia e inovação.',
        'social_links' => [
            'instagram' => 'https://instagram.com/eventhub',
        ],
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.name', 'EventHub Produções')
        ->assertJsonPath('data.slug', 'eventhub-producoes')
        ->assertJsonPath(
            'data.social_links.instagram',
            'https://instagram.com/eventhub',
        );

    $organizer = Organizer::query()
        ->where('slug', 'eventhub-producoes')
        ->firstOrFail();

    $user = $user->fresh();

    $membership = $user->organizers()
        ->where('organizers.id', $organizer->id)
        ->firstOrFail();

    expect($membership->pivot->role)->toBe('owner');
    expect($user->roles()->pluck('name')->all())->toContain('organizer');
});

test('a visitor cannot create an organizer', function (): void {
    $this->postJson('/api/v1/organizers', [
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ])->assertUnauthorized();
});

test('an organizer slug must be unique', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    Organizer::query()->create([
        'name' => 'Existing Organizer',
        'slug' => 'eventhub-producoes',
    ]);

    $this->postJson('/api/v1/organizers', [
        'name' => 'Another Organizer',
        'slug' => 'eventhub-producoes',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['slug']);
});