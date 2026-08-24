<?php

use App\Models\Category;
use App\Models\Event;
use App\Models\Order;
use App\Models\Organizer;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function orderRelationshipContext(): array
{
    $user = User::factory()->create();

    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);
    $category = Category::query()->create([
        'name' => 'Tecnologia',
        'slug' => 'tecnologia',
    ]);
    $event = $organizer->events()->create([
        'category_id' => $category->id,
        'name' => 'Laravel Conference',
        'slug' => 'laravel-conference-2026',
        'description' => 'Evento sobre Laravel.',
        'starts_at' => now()->addDays(10)->toISOString(),
        'ends_at' => now()->addDays(10)->addHours(8)->toISOString(),
        'venue_name' => 'Centro de Convenções',
        'address_line' => 'Rua Principal, 100',
        'city' => 'São Paulo',
        'state' => 'SP',
        'postal_code' => '01001-000',
        'capacity' => 500,
    ]);
    $ticketType = $event->ticketTypes()->create([
        'name' => 'VIP',
        'price_in_cents' => 30000,
        'quantity' => 100,
    ]);

    return [$user, $event, $ticketType];
}

function createOrderForRelationship(User $user, Event $event): Order
{
    $order = new Order([
        'status' => 'pending',
        'total_in_cents' => 60000,
    ]);

    $order->code = Str::uuid()->toString();
    $order->user()->associate($user);
    $order->event()->associate($event);
    $order->save();

    return $order;
}

test('an order belongs to a user and an event and has items', function (): void {
    [$user, $event, $ticketType] = orderRelationshipContext();

    $order = createOrderForRelationship($user, $event);

    $item = $order->items()->create([
        'ticket_type_id' => $ticketType->id,
        'ticket_type_name' => 'VIP',
        'unit_price_in_cents' => 30000,
        'quantity' => 2,
        'total_in_cents' => 60000,
    ]);

    expect($order->user->is($user))->toBeTrue();
    expect($order->event->is($event))->toBeTrue();
    expect($order->items)->toHaveCount(1);
    expect($order->items->first()->is($item))->toBeTrue();
});

test('an order item belongs to its ticket type', function (): void {
    [$user, $event, $ticketType] = orderRelationshipContext();

    $order = createOrderForRelationship($user, $event);

    $item = $order->items()->create([
        'ticket_type_id' => $ticketType->id,
        'ticket_type_name' => 'VIP',
        'unit_price_in_cents' => 30000,
        'quantity' => 2,
        'total_in_cents' => 60000,
    ]);

    expect($item->ticketType->is($ticketType))->toBeTrue();
    expect($ticketType->orderItems)->toHaveCount(1);
});