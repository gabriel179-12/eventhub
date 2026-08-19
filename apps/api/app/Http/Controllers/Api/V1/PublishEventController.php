<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Events\Actions\PublishEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\EventResource;
use App\Models\Event;
use App\Models\Organizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class PublishEventController extends Controller
{
    public function __invoke(
        Organizer $organizer,
        Event $event,
        publishEvent $publishEvent
    ): JsonResponse{
        Gate::authorize('publishEvent', [$organizer, $event]);

        $event = $publishEvent->handle($event);

        return (new EventResource($event))->response();
    }
}
