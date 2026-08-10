<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'Can manage the platform.',
            ],
            [
                'name' => 'organizer',
                'description' => 'Can manage events for an organization.',
            ],
            [
                'name' => 'participant',
                'description' => 'Can buy tickets and review events.',
            ],
        ];

        foreach ($roles as $role) {
            Role::query()->updateOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']],
            );
        }
    }
}