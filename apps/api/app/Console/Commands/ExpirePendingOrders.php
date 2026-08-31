<?php

namespace App\Console\Commands;

use App\Domains\Orders\Actions\ExpirePendingOrders as ExpirePendingOrdersAction;
use Illuminate\Console\Command;

class ExpirePendingOrders extends Command
{
    protected $signature = 'orders:expire';

    protected $description = 'Expires overdue pending orders and releases ticket reservations';

    public function handle(ExpirePendingOrdersAction $expirePendingOrders): int
    {
        $expiredOrders = $expirePendingOrders->handle();

        $this->info("{$expiredOrders} order(s) expired.");

        return self::SUCCESS;
    }
}
