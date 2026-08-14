<?php

namespace App\Domains\Events\Actions;

use App\Models\Event;
use App\Models\Organizer;

final class CreateEvent
{
    /**
     * @param array{
     *     category_id: int,
     *     name: string,
     *     slug: string,
     *     description: string,
     *     starts_at: string,
     *     ends_at?: string|null,
     *     venue_name: string,
     *     address_line: string,
     *     city: string,
     *     state: string,
     *     postal_code: string,
     *     latitude?: float|null,
     *     longitude?: float|null,
     *     capacity?: int|null,
     *     is_private?: bool
     * } $attributes
     */
    public function handle(Organizer $organizer, array $attributes): Event
    {
        $event = $organizer->events()->create([
            'category_id' => $attributes['category_id'],
            'name' => $attributes['name'],
            'slug' => $attributes['slug'],
            'description' => $attributes['description'],
            'starts_at' => $attributes['starts_at'],
            'ends_at' => $attributes['ends_at'] ?? null,
            'venue_name' => $attributes['venue_name'],
            'address_line' => $attributes['address_line'],
            'city' => $attributes['city'],
            'state' => $attributes['state'],
            'postal_code' => $attributes['postal_code'],
            'latitude' => $attributes['latitude'] ?? null,
            'longitude' => $attributes['longitude'] ?? null,
            'capacity' => $attributes['capacity'] ?? null,
            'is_private' => $attributes['is_private'] ?? false,
            'status' => 'draft',
            'is_private' => $attributes['is_private'] ?? false,
        ]);

        return $event->refresh();
    }
}