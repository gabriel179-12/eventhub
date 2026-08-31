<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    protected $fillable = [
        'ticket_type_id',
        'ticket_type_name',
        'unit_price_in_cents',
        'quantity',
        'total_in_cents',
    ];

    protected function casts(): array
    {
        return [
            'unit_price_in_cents' => 'integer',
            'quantity' => 'integer',
            'total_in_cents' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}