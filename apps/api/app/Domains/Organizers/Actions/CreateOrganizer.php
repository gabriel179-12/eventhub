<?php

namespace App\Domains\Organizers\Actions;

use App\Models\Organizer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CreateOrganizer
{
    /**
     * @param array{
     *     name: string,
     *     slug: string,
     *     description?: string|null,
     *     social_links?: array<string, string>|null
     * } $attributes
     */
    public function handle(User $user, array $attributes): Organizer
    {
        return DB::transaction(function () use ($user, $attributes): Organizer {
            $organizer = Organizer::query()->create([
                'name' => $attributes['name'],
                'slug' => $attributes['slug'],
                'description' => $attributes['description'] ?? null,
                'social_links' => $attributes['social_links'] ?? null,
            ]);

            $user->organizers()->attach($organizer->id, [
                'role' => 'owner',
            ]);

            $organizerRole = Role::query()
                ->where('name', 'organizer')
                ->firstOrFail();

            $user->roles()->syncWithoutDetaching([
                $organizerRole->id,
            ]);

            return $organizer;
        });
    }
}