<?php

namespace App\Domains\Organizers\Actions;

use App\Models\Organizer;

final class UpdateOrganizer
{
    /**
     * @param array{
     *      name?: string,
     *      description?: string|null,
     *      social_links?: array<string, string>|null 
     * } $attributes
     */

     public function handle(Organizer $organizer, array $attributes): Organizer
     {
        $organizer->fill($attributes);
        $organizer->save();

        return $organizer->refresh();
     }
}