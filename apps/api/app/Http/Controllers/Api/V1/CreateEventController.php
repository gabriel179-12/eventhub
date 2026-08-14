<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Events\Actions\CreateEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Events\StoreEventRequest;
use App\Http\Resources\Api\V1\EventResource;
use App\Models\Organizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

final class CreateEventController extends Controller
{
    public function __invoke(
        StoreEventRequest $request,
        Organizer $organizer,
        CreateEvent $createEvent,
    ): JsonResponse {
        Gate::authorize('createEvent', $organizer);

        $event = $createEvent->handle(
            $organizer,
            $request->validated(),
        );

        return (new EventResource($event))
            ->response()
            ->setStatusCode(201);
    }
}