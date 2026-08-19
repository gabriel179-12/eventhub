<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\EventResource;
use App\Models\Event;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListPublicEventsController extends Controller
{
    public function __invoke(): AnonymousResourceCollection
    {
        $events = Event::query()
            ->publicUpcoming()
            ->orderBy('starts_at')
            ->get();

        return EventResource::collection($events);
    }
}
