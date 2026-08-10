<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a user can have multiple roles', function (): void {
    $user = User::factory()->create();

    $participant = Role::query()->create([
        'name' => 'participant',
        'description' => 'Can buy tickets.',
    ]);

    $organizer = Role::query()->create([
        'name' => 'organizer',
        'description' => 'Can manage events.',
    ]);

    $user->roles()->attach([
        $participant->id,
        $organizer->id,
    ]);

    $roleNames = $user->roles()->pluck('name')->all();

    expect($roleNames)
        ->toHaveCount(2)
        ->toContain('participant')
        ->toContain('organizer');
});

test('a role can be assigned to multiple users', function (): void {
    $organizer = Role::query()->create([
        'name' => 'organizer',
        'description' => 'Can manage events.',
    ]);

    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();

    $organizer->users()->attach([
        $firstUser->id,
        $secondUser->id,
    ]);

    $userIds = $organizer->users()->pluck('id')->all();

    expect($userIds)
        ->toHaveCount(2)
        ->toContain($firstUser->id)
        ->toContain($secondUser->id);
});