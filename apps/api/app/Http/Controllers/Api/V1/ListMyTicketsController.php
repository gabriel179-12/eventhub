<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TicketResource;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListMyTicketsController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $tickets = Ticket::query()
            ->whereHas('orderItem.order', function (Builder $query) use ($request): void {
                $query->where('user_id', $request->user()->id);
            })
            ->orderByDesc('id')
            ->paginate(20);

        return TicketResource::collection($tickets);
    }
}
