<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Orders\Actions\PayOrder;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Order;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class PayOrderController extends Controller
{
    public function __invoke(
        Order $order,
        PayOrder $payOrder,
    ): OrderResource{
        Gate::authorize('pay', $order);

        $paiOrder = $payOrder->handle($order);

        if ($paiOrder === null) {
            throw ValidationException::withMessages([
                'order' => ['O pedido não pode ser pago.'],
            ]);
        }

        return new OrderResource($paiOrder);
    }
}
