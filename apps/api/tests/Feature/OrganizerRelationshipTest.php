<?php

use App\Models\Organizer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an organizer can have team members with scoped roles', function (): void {
    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);

    $owner = User::factory()->create();
    $staff = User::factory()->create();

    $organizer->users()->attach([
        $owner->id => ['role' => 'owner'],
        $staff->id => ['role' => 'checkin_staff'],
    ]);

    $ownerMembership = $organizer->users()
        ->where('users.id', $owner->id)
        ->firstOrFail();

    $staffMembership = $organizer->users()
        ->where('users.id', $staff->id)
        ->firstOrFail();

    expect($organizer->users)->toHaveCount(2);
    expect($ownerMembership->pivot->role)->toBe('owner');
    expect($staffMembership->pivot->role)->toBe('checkin_staff');
});

test('a user can belong to organizations with different roles', function (): void {
    $user = User::factory()->create();

    $firstOrganizer = Organizer::query()->create([
        'name' => 'Primeira Produções',
        'slug' => 'primeira-producoes',
    ]);

    $secondOrganizer = Organizer::query()->create([
        'name' => 'Segunda Eventos',
        'slug' => 'segunda-eventos',
    ]);

    $user->organizers()->attach([
        $firstOrganizer->id => ['role' => 'owner'],
        $secondOrganizer->id => ['role' => 'manager'],
    ]);

    $memberships = $user->organizers()->get()->keyBy('id');

    expect($memberships)->toHaveCount(2);
    expect($memberships->get($firstOrganizer->id)->pivot->role)->toBe('owner');
    expect($memberships->get($secondOrganizer->id)->pivot->role)->toBe('manager');
});