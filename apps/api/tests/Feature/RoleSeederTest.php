<?php

use App\Models\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the role seeder creates the canonical roles', function (): void {
    $this->seed(RoleSeeder::class);

    expect(Role::query()->orderBy('name')->pluck('name')->all())
        ->toBe([
            'admin',
            'organizer',
            'participant',
        ]);
});

test('the role seeder can be executed more than once', function (): void {
    $this->seed(RoleSeeder::class);
    $this->seed(RoleSeeder::class);

    expect(Role::query()->count())
        ->toBe(3);
});

test('the database seeder includes the canonical roles', function (): void {
    $this->seed();

    expect(Role::query()->count())
        ->toBe(3);
});