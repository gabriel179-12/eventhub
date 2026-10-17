<?php

namespace App\Domains\Tickets\Actions;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CheckInTicket
{
    public function handle(Event $event, User $staff, string $code): Ticket
    {
        return DB::transaction(function () use ($event, $staff, $code): Ticket{
            $ticket = Ticket::query()
                ->where('code', $code)
                ->whereHas('orderItem.order', function (Builder $query) use ($event): void{
                    $query->where('event_id', $event->id)
                        ->where('status', 'paid');
                })
                ->lockForUpdate()
                ->firstOrFail();
            if (
                $ticket->status !== 'valid'
                || $ticket->checked_in_at !== null
            ) {
                throw ValidationException::withMessages([
                    'code' => ['O ingresso já foi utilizado ou está cancelado.'],
                ]);
            }

            $ticket->status = 'used';
            $ticket->checked_in_at = now();
            $ticket->checkedInBy()->associate($staff);
            $ticket->save();

            return $ticket;
        });
    }
}