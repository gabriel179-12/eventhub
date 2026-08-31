<?php

namespace App\Domains\Orders\Actions;

use App\Models\Order;
use App\Models\TicketType;
use Illuminate\Support\Facades\DB;

final class ExpirePendingOrders
{
    public function handle(): int
    {
        $orderIds = Order::query()
            ->where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->pluck('id');

        $expiredOrders = 0;

        foreach ($orderIds as $orderId) {
            if ($this->expireOrder($orderId)) {
                $expiredOrders++;
            }
        }

        return $expiredOrders;
    }

    private function expireOrder(int $orderId): bool
    {
        return DB::transaction(function () use ($orderId): bool {
            $order = Order::query()
                ->lockForUpdate()
                ->findOrFail($orderId);
                if (
                    $order->status !== 'pending'
                    || $order->expires_at === null
                    || $order->expires_at->isFuture()
                ) {
                    return false;
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

                foreach ($items as $item) {
                    $ticketTypes = $ticketTypes->get($item->ticket_type_id);

                    $ticketTypes->decrement('quantity_reserved', $item->quantity);
                }

                $order->status = 'expired';
                $order->save();

                return true;
        });
    }
}