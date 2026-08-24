<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\EventResource;
use App\Models\Event;

final class ShowPublicEventController extends Controller
{
    public function __invoke(string $slug): EventResource
    {
        $event = Event::query()
            ->publicUpcoming()
            ->with([
                'organizer',
                'category',
                'ticketTypes' => fn ($query) => $query
                    ->availableForSale()
                    ->orderBy('price_in_cents'),
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        return new EventResource($event);
    }
}
