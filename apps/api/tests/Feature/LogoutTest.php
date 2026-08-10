<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses (RefreshDatabase::class);

test('an authenticated user can logout', function (): void {
    $password = 'Password123!';

    User::factory()->create([
        'email' => 'gabriel@example.com',
        'password' => Hash::make($password),
    ]);

    $headers = [
        'Origin' => 'http://127.0.0.1:8080',
        'Sec-Fetch-Site' => 'same-origin',
    ];

    $this->withHeaders($headers)
        ->withSession([])
        ->postJson('/api/v1/auth/login', [
            'email' => 'gabriel@example.com',
            'password' => $password,
        ])
        ->assertOk();

    $this->withHeaders($headers)
        ->postJson('/api/v1/auth/logout')
        ->assertNoContent();

    $this->assertGuest('web');
});

test('an unauthenticated user cannot logout', function (): void{
    $this->postJson('/api/v1/auth/logout')
        ->assertUnauthorized();
});