<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('an authenticated user can view their profile', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', $user->email)
        ->assertJsonMissingPath('data.password');
});

test('an unauthenticated user cannot view a profile', function (): void {
    $this->getJson('/api/v1/me')
        ->assertUnauthorized();
});