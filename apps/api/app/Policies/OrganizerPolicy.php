<?php

namespace App\Policies;

use App\Models\Organizer;
use App\Models\User;


final class OrganizerPolicy
{
    public function manageTeam(User $user, Organizer $organizer): bool
    {
        return $organizer->users()
            ->where('users.id', $user->id)
            ->wherePivot('role', 'owner')
            ->exists();
    }

    public function update(User $user, Organizer $organizer): bool
    {
        return $this->manageTeam($user, $organizer);
    }
}
