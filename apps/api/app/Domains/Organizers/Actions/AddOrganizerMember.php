<?php

namespace App\Domains\Organizers\Actions;

use App\Models\Organizer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AddOrganizerMember
{
    /**
     * @param array{email: string, role: string} $attributes
     */
    public function handle(Organizer $organizer, array $attributes): User
    {
        return DB::transaction(function () use ($organizer, $attributes): User {
            $member = User::query()
                ->where('email', $attributes['email'])
                ->firstOrFail();

            $alreadyMember = $organizer->users()
                ->where('users.id', $member->id)
                ->exists();

            if ($alreadyMember) {
                throw ValidationException::withMessages([
                    'email' => ['This user already belongs to the organizer.'],
                ]);
            }

            $organizer->users()->attach($member->id, [
                'role' => $attributes['role'],
            ]);

            $organizerRole = Role::query()
                ->where('name', 'organizer')
                ->firstOrFail();

            $member->roles()->syncWithoutDetaching([
                $organizerRole->id,
            ]);

            return $member;
        });
    }
}