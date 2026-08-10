<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('a registered user can login', function (): void {
    $user = User::factory()->create([
        'email' => 'gabriel@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $this->withHeaders([
        'Origin' => 'http://127.0.0.1:8080',
        'Sec-Fetch-Site' => 'same-origin',
    ])
        ->postJson('/api/v1/auth/login', [
            'email' => 'gabriel@example.com',
            'password' => 'Password123!',
        ])
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', $user->email)
        ->assertJsonMissingPath('data.password');

    $this->assertAuthenticatedAs($user);
});

test('a user cannot login with invalid credentials', function (): void {
    User::factory()->create([
        'email' => 'gabriel@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $this->withHeaders([
        'Origin' => 'http://127.0.0.1:8080',
        'Sec-Fetch-Site' => 'same-origin',
    ])
        ->postJson('/api/v1/auth/login', [
            'email' => 'gabriel@example.com',
            'password' => 'wrong-password',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});