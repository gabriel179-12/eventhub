<?php

use App\Models\Category;
use App\Models\Event;
use App\Models\Order;
use App\Models\Organizer;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function pendingOrderForPayment(User $user, array $orderOverrides = []): array
{
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

    $ticketType->quantity_reserved = 2;
    $ticketType->save();

    $order = new Order(array_merge([
        'status' => 'pending',
        'total_in_cents' => 60000,
        'expires_at' => now()->addMinutes(15),
    ], $orderOverrides));

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

test('a user can pay their pending order', function (): void {
    $user = User::factory()->create();
    [$order, $ticketType] = pendingOrderForPayment($user);

    Sanctum::actingAs($user);

    $this->patchJson("/api/v1/orders/{$order->code}/pay")
        ->assertOk()
        ->assertJsonPath('data.status', 'paid')
        ->assertJsonPath('data.total_in_cents', 60000);

    expect($order->fresh()->status)->toBe('paid');
    expect($order->fresh()->paid_at)->not->toBeNull();
    expect($ticketType->fresh()->quantity_reserved)->toBe(0);
    expect($ticketType->fresh()->quantity_sold)->toBe(2);
});

test('a user cannot pay another users order', function (): void {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    [$order] = pendingOrderForPayment($owner);

    Sanctum::actingAs($otherUser);

    $this->patchJson("/api/v1/orders/{$order->code}/pay")
        ->assertForbidden();

    expect($order->fresh()->status)->toBe('pending');
});

test('an expired order cannot be paid and releases its reservations', function (): void {
    $user = User::factory()->create();

    [$order, $ticketType] = pendingOrderForPayment($user, [
        'expires_at' => now()->subMinute(),
    ]);

    Sanctum::actingAs($user);

    $this->patchJson("/api/v1/orders/{$order->code}/pay")
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['order']);

    expect($order->fresh()->status)->toBe('expired');
    expect($ticketType->fresh()->quantity_reserved)->toBe(0);
});