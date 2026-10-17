<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\Organizer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->staff = User::factory()->create();
    $buyer = User::factory()->create();

    $this->organizer = Organizer::query()->create([
        'name' => 'EventHub Produções',
        'slug' => 'eventhub-producoes',
    ]);

    $category = Category::query()->create([
        'name' => 'Tecnologia',
        'slug' => 'tecnologia',
    ]);

    $this->event = $this->organizer->events()->create([
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

    $ticketType = $this->event->ticketTypes()->create([
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
    $order->user()->associate($buyer);
    $order->event()->associate($this->event);
    $order->save();

    $item = $order->items()->create([
        'ticket_type_id' => $ticketType->id,
        'ticket_type_name' => 'VIP',
        'unit_price_in_cents' => 30000,
        'quantity' => 1,
        'total_in_cents' => 30000,
    ]);

    $this->ticket = $item->tickets()->create([
        'code' => Str::uuid()->toString(),
        'status' => 'valid',
    ]);

    $this->endpoint = "/api/v1/organizers/{$this->organizer->slug}"
        . "/events/{$this->event->slug}/check-ins";
});

test('authorized team members can check in a ticket', function (string $role): void {
    $this->organizer->users()->attach($this->staff->id, [
        'role' => $role,
    ]);

    Sanctum::actingAs($this->staff);

    $this->postJson($this->endpoint, [
        'code' => $this->ticket->code,
    ])
        ->assertOk()
        ->assertJsonPath('data.code', $this->ticket->code)
        ->assertJsonPath('data.status', 'used');

    $ticket = $this->ticket->fresh();

    expect($ticket->checked_in_at)->not->toBeNull();
    expect($ticket->checked_in_by)->toBe($this->staff->id);
})->with(['owner', 'manager', 'checkin_staff']);

test('a ticket cannot be checked in twice', function (): void {
    $this->organizer->users()->attach($this->staff->id, [
        'role' => 'checkin_staff',
    ]);

    Sanctum::actingAs($this->staff);

    $payload = ['code' => $this->ticket->code];

    $this->postJson($this->endpoint, $payload)->assertOk();

    $checkedInAt = $this->ticket->fresh()->checked_in_at->toISOString();

    $this->postJson($this->endpoint, $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['code']);

    expect($this->ticket->fresh()->checked_in_at->toISOString())
        ->toBe($checkedInAt);
});

test('a user outside the organizer team cannot check in a ticket', function (): void {
    Sanctum::actingAs($this->staff);

    $this->postJson($this->endpoint, [
        'code' => $this->ticket->code,
    ])->assertForbidden();

    expect($this->ticket->fresh()->status)->toBe('valid');
    expect($this->ticket->fresh()->checked_in_at)->toBeNull();
});

test('a visitor cannot check in a ticket', function (): void {
    $this->postJson($this->endpoint, [
        'code' => $this->ticket->code,
    ])->assertUnauthorized();
});