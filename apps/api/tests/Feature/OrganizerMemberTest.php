<?php

use App\Models\Organizer;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('an owner can add a manager to the organizer team', function (): void {
    $this->seed(RoleSeeder::class);

    $owner = User::factory()->create();
    $member = User::factory()->create([
        'email' => 'manager@example.com',
    ]);

    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);

    $owner->organizers()->attach($organizer->id, [
        'role' => 'owner',
    ]);

    Sanctum::actingAs($owner);

    $this->postJson('/api/v1/organizers/eventhub-producoes/members', [
        'email' => 'manager@example.com',
        'role' => 'manager',
    ])->assertCreated();

    $membership = $organizer->users()
        ->where('users.id', $member->id)
        ->firstOrFail();

    expect($membership->pivot->role)->toBe('manager');
    expect($member->fresh()->roles()->pluck('name')->all())
        ->toContain('organizer');
});

test('a non-owner cannot add organizer members', function (): void {
    $manager = User::factory()->create();
    $newMember = User::factory()->create([
        'email' => 'new-member@example.com',
    ]);

    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);

    $manager->organizers()->attach($organizer->id, [
        'role' => 'manager',
    ]);

    Sanctum::actingAs($manager);

    $this->postJson('/api/v1/organizers/eventhub-producoes/members', [
        'email' => 'new-member@example.com',
        'role' => 'checkin_staff',
    ])->assertForbidden();
});

test('an owner can only add allowed team roles', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create([
        'email' => 'new-member@example.com',
    ]);

    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);

    $owner->organizers()->attach($organizer->id, [
        'role' => 'owner',
    ]);

    Sanctum::actingAs($owner);

    $this->postJson('/api/v1/organizers/eventhub-producoes/members', [
        'email' => 'new-member@example.com',
        'role' => 'owner',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['role']);
});

test('an owner cannot add the same member twice', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create([
        'email' => 'member@example.com',
    ]);

    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);

    $owner->organizers()->attach($organizer->id, [
        'role' => 'owner',
    ]);

    $organizer->users()->attach($member->id, [
        'role' => 'manager',
    ]);

    Sanctum::actingAs($owner);

    $this->postJson('/api/v1/organizers/eventhub-producoes/members', [
        'email' => 'member@example.com',
        'role' => 'checkin_staff',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});