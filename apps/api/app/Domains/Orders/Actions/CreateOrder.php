<?php

namespace App\Domains\Orders\Actions;

use App\Models\Event;
use App\Models\Order;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CreateOrder
{
    /**
     * @param array{
     *     items: array<int, array{ticket_type_id: int, quantity: int}>
     * } $attributes
     */
    public function handle(User $user, Event $event, array $attributes): Order
    {
        return DB::transaction(function () use ($user, $event, $attributes): Order {
            $ticketTypeIds = collect($attributes['items'])
                ->pluck('ticket_type_id')
                ->sort()
                ->values()
                ->all();

            $ticketTypes = TicketType::query()
                ->where('event_id', $event->id)
                ->whereIn('id', $ticketTypeIds)
                ->availableForSale()
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $order = new Order([
                'status' => 'pending',
                'total_in_cents' => 0,
                'expires_at' => now()->addMinutes(15),
            ]);

            $order->code = Str::uuid()->toString();
            $order->user()->associate($user);
            $order->event()->associate($event);
            $order->save();

            $totalInCents = 0;

            foreach ($attributes['items'] as $index => $item) {
                /** @var TicketType|null $ticketType */
                $ticketType = $ticketTypes->get($item['ticket_type_id']);

                if ($ticketType === null) {
                    throw ValidationException::withMessages([
                        "items.{$index}.ticket_type_id" => [
                            'O tipo de ingresso não está disponível para este evento.',
                        ],
                    ]);
                }

                $availableQuantity = $ticketType->quantity
                    - $ticketType->quantity_sold
                    - $ticketType->quantity_reserved;

                if ($item['quantity'] > $availableQuantity) {
                    throw ValidationException::withMessages([
                        "items.{$index}.quantity" => [
                            'Não há ingressos suficientes disponíveis.',
                        ],
                    ]);
                }

                $itemTotalInCents = $ticketType->price_in_cents * $item['quantity'];

                $order->items()->create([
                    'ticket_type_id' => $ticketType->id,
                    'ticket_type_name' => $ticketType->name,
                    'unit_price_in_cents' => $ticketType->price_in_cents,
                    'quantity' => $item['quantity'],
                    'total_in_cents' => $itemTotalInCents,
                ]);

                $ticketType->increment('quantity_reserved', $item['quantity']);

                $totalInCents += $itemTotalInCents;
            }

            $order->update([
                'total_in_cents' => $totalInCents,
            ]);

            return $order->load('items');
        });
    }
}