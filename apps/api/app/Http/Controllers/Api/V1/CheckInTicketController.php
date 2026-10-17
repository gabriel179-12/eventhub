<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Tickets\Actions\CheckInTicket;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tickets\CheckInTicketRequest;
use App\Http\Resources\Api\V1\TicketResource;
use App\Models\Event;
use App\Models\Organizer;
use Illuminate\Http\Request;

class CheckInTicketController extends Controller
{
    /**
     * Handle the incoming request.
     */
     public function __invoke(
        CheckInTicketRequest $request,
        Organizer $organizer,
        Event $event,
        CheckInTicket $checkInTicket,
    ): TicketResource {
        $ticket = $checkInTicket->handle(
            $event,
            $request->user(),
            $request->validated('code'),
        );

        return new TicketResource($ticket);
    }
}