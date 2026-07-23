<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('a visitor can register', function (): void{
    $password = 'Password123!';

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Gabriel Souza',
        'email' => 'gabriel@example.com',
        'password' => $password,
        'password_confirmation' => $password,
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.name', 'Gabriel Souza')
        ->assertJsonPath('data.email', 'gabriel@example.com')
        ->assertJsonMissingPath('data.password');

    $user = User::query()
        ->where('email', 'gabriel@example.com')
        ->firstOrFail();
    
    expect(Hash::check($password, $user->password))->toBeTrue();
});

test('a visitor cannot register with an email already in use', function (): void {
    User::factory()->create([
        'email' => 'gabriel@example.com'
    ]);

    $this->postJson('/api/v1/auth/register', [
        'name' => 'Another User',
        'email' => 'gabriel@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('a visitor must confirm the password when registering', function (): void {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Gabriel Souza',
        'email' => 'gabriel@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'different-password',
    ])
    ->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});