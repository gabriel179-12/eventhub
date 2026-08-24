<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Orders\Actions\CreateOrder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\StoreOrderRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Event;
use Illuminate\Http\Response;

class CreateOrderController extends Controller
{
    public function __invoke(
        StoreOrderRequest $request,
        Event $event,
        CreateOrder $createOrder,
    ): OrderResource {
        abort_unless(
            $event->status === 'published'
            && ! $event->is_private
            && $event->starts_at->isFuture(),
            Response::HTTP_NOT_FOUND,
        );

        $order = $createOrder->handle(
            $request->user(),
            $event,
            $request->validated(),
        );

        return new OrderResource($order);
    }
}