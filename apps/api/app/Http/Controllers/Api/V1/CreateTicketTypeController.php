<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Tickets\Actions\CreateTicketType;
use App\Http\Controllers\Controller;
use App\Http\Requests\TicketTypes\StoreTicketTypeRequest;
use App\Http\Resources\Api\V1\TicketTypeResource;
use App\Models\Event;
use App\Models\Organizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

final class CreateTicketTypeController extends Controller
{
   public function __invoke(
        StoreTicketTypeRequest $request,
        Organizer $organizer,
        Event $event,
        CreateTicketType $createTicketType,
   ): JsonResponse{
        Gate::authorize('manageTicketTypes', [$organizer, $event]);

        $ticketType = $createTicketType->handle(
            $event,
            $request->validated(),
        );
        return (new TicketTypeResource($ticketType))
            ->response()
            ->setStatusCode(201);
   }
}
