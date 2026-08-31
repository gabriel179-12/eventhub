<?php

namespace App\Domains\Orders\Actions;

use App\Models\Order;
use App\Models\TicketType;
use Illuminate\Support\Facades\DB;

final class PayOrder
{
    public function handle(Order $order): ?Order
    {
        return DB::transaction(function () use ($order): ?Order {
            $order = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ($order->status !== 'pending'){
                return null;
            }

            $items = $order->items()
                ->orderBy('ticket_type_id')
                ->get();
            $ticketTypes = TicketType::query()
                ->whereIn('id', $items->pluck('ticket_type_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($order->expires_at?->isPast()) {
                foreach ($items as $item) {
                    $ticketTypes
                        ->get($item->ticket_type_id)
                        ->decrement('quantity_reserved', $item->quantity);
                }

                $order->status = 'expired';
                $order->save();

                return null;
            }

            foreach ($items as $item) {
                $ticketType = $ticketTypes->get($item->ticket_type_id);
                $ticketType->decrement('quantity_reserved', $item->quantity);
                $ticketType->increment('quantity_sold', $item->quantity);
            }

            $order->status = 'paid';
            $order->paid_at = now();
            $order->save();

            return $order->load('items');
        });
    }
}