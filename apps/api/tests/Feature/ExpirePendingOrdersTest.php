<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\Organizer;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function pendingOrderForExpiration(
    User $user,
    string $suffix,
    DateTimeInterface $expiresAt,
): array {
    $organizer = Organizer::query()->create([
        'name' => "Produções {$suffix}",
        'slug' => "producoes-{$suffix}",
    ]);

    $category = Category::query()->create([
        'name' => "Tecnologia {$suffix}",
        'slug' => "tecnologia-{$suffix}",
    ]);

    $event = $organizer->events()->create([
        'category_id' => $category->id,
        'name' => "Laravel Conference {$suffix}",
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

    $ticketType->quantity_reserved = 2;
    $ticketType->save();

    $order = new Order([
        'status' => 'pending',
        'total_in_cents' => 60000,
        'expires_at' => $expiresAt,
    ]);

    $order->code = Str::uuid()->toString();
    $order->user()->associate($user);
    $order->event()->associate($event);
    $order->save();

    $order->items()->create([
        'ticket_type_id' => $ticketType->id,
        'ticket_type_name' => 'VIP',
        'unit_price_in_cents' => 30000,
        'quantity' => 2,
        'total_in_cents' => 60000,
    ]);

    return [$order, $ticketType];
}

test('the command expires overdue pending orders and releases reservations', function (): void {
    $user = User::factory()->create();
    [$order, $ticketType] = pendingOrderForExpiration(
        $user,
        'vencido',
        now()->subMinute(),
    );

    $this->artisan('orders:expire')
        ->assertSuccessful();

    expect($order->fresh()->status)->toBe('expired');
    expect($ticketType->fresh()->quantity_reserved)->toBe(0);
});

test('the command keeps active and paid orders unchanged', function (): void {
    $user = User::factory()->create();

    [$activeOrder, $activeTicketType] = pendingOrderForExpiration(
        $user,
        'ativo',
        now()->addMinutes(15),
    );

    [$paidOrder, $paidTicketType] = pendingOrderForExpiration(
        $user,
        'pago',
        now()->subMinute(),
    );

    $paidOrder->status = 'paid';
    $paidOrder->paid_at = now();
    $paidOrder->save();

    $paidTicketType->quantity_reserved = 0;
    $paidTicketType->quantity_sold = 2;
    $paidTicketType->save();

    $this->artisan('orders:expire')
        ->assertSuccessful();

    expect($activeOrder->fresh()->status)->toBe('pending');
    expect($activeTicketType->fresh()->quantity_reserved)->toBe(2);
    expect($paidOrder->fresh()->status)->toBe('paid');
    expect($paidTicketType->fresh()->quantity_sold)->toBe(2);
});