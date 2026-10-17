<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\Organizer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function ticketForMyTicketIndex(User $user): Ticket
{
    $suffix = Str::uuid()->toString();

    $organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => "producoes-{$suffix}",
    ]);

    $category = Category::query()->create([
        'name' => "Tecnologia {$suffix}",
        'slug' => "tecnologia-{$suffix}",
    ]);

    $event = $organizer->events()->create([
        'category_id' => $category->id,
        'name' => 'Laravel Conference',
        'slug' => "laravel-conference-{$suffix}",
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

    $item = $order->items()->create([
        'ticket_type_id' => $ticketType->id,
        'ticket_type_name' => 'VIP',
        'unit_price_in_cents' => 30000,
        'quantity' => 1,
        'total_in_cents' => 30000,
    ]);

    return $item->tickets()->create([
        'code' => Str::uuid()->toString(),
        'status' => 'valid',
    ]);
}

test('a user sees only their own tickets', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $myTicket = ticketForMyTicketIndex($user);
    $otherTicket = ticketForMyTicketIndex($otherUser);

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/me/tickets')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.code', $myTicket->code)
        ->assertJsonPath('data.0.status', 'valid')
        ->assertJsonMissing(['code' => $otherTicket->code]);
});

test('a user without tickets receives an empty list', function (): void {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/v1/me/tickets')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

test('a visitor cannot list tickets', function (): void {
    $this->getJson('/api/v1/me/tickets')
        ->assertUnauthorized();
});