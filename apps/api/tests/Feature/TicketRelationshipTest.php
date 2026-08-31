<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Organizer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function ticketRelationshipContext(): OrderItem
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
        'starts_at' => now()->addDays(10),
        'ends_at' => now()->addDays(10)->addHours(8),
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

    $order = new Order([
        'status' => 'paid',
        'total_in_cents' => 30000,
        'paid_at' => now(),
    ]);

    $order->code = Str::uuid()->toString();
    $order->user()->associate($user);
    $order->event()->associate($event);
    $order->save();

    return $order->items()->create([
        'ticket_type_id' => $ticketType->id,
        'ticket_type_name' => 'VIP',
        'unit_price_in_cents' => 30000,
        'quantity' => 1,
        'total_in_cents' => 30000,
    ]);
}

test('a ticket belongs to an order item', function (): void {
    $orderItem = ticketRelationshipContext();

    $ticket = $orderItem->tickets()->create([
        'code' => Str::uuid()->toString(),
        'status' => 'valid',
    ]);

    expect($ticket->orderItem->is($orderItem))->toBeTrue();
    expect($orderItem->tickets)->toHaveCount(1);
    expect($orderItem->tickets->first()->is($ticket))->toBeTrue();
});

test('a ticket can record the user who checked it in', function (): void {
    $orderItem = ticketRelationshipContext();
    $checkinStaff = User::factory()->create();

    $ticket = $orderItem->tickets()->create([
        'code' => Str::uuid()->toString(),
        'status' => 'valid',
    ]);

    $ticket->status = 'used';
    $ticket->checked_in_at = now();
    $ticket->checked_in_by = $checkinStaff->id;
    $ticket->save();

    expect($ticket->checkedInBy->is($checkinStaff))->toBeTrue();
    expect($checkinStaff->checkedInTickets)->toHaveCount(1);
    expect($checkinStaff->checkedInTickets->first()->is($ticket))->toBeTrue();
});