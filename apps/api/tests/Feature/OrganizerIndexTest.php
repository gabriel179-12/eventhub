<?php

use App\Models\Organizer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('an authenticated user sees only their organizers', function (): void {
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();

    $firstOrganizer = Organizer::query()->create([
        'name' => 'Primeira Produções',
        'slug' => 'primeira-producoes',
    ]);

    $secondOrganizer = Organizer::query()->create([
        'name' => 'Segunda Eventos',
        'slug' => 'segunda-eventos',
    ]);

    $externalOrganizer = Organizer::query()->create([
        'name' => 'Empresa Externa',
        'slug' => 'empresa-externa',
    ]);

    $user->organizers()->attach([
        $firstOrganizer->id => ['role' => 'owner'],
        $secondOrganizer->id => ['role' => 'manager'],
    ]);

    $anotherUser->organizers()->attach([
        $externalOrganizer->id => ['role' => 'owner'],
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/organizers')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment([
            'slug' => 'primeira-producoes',
            'membership_role' => 'owner',
        ])
        ->assertJsonFragment([
            'slug' => 'segunda-eventos',
            'membership_role' => 'manager',
        ])
        ->assertJsonMissing([
            'slug' => 'empresa-externa',
        ]);
});

test('a visitor cannot list organizers', function (): void {
    $this->getJson('/api/v1/organizers')
        ->assertUnauthorized();
});