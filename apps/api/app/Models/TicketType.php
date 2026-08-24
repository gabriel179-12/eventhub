<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class TicketType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price_in_cents',
        'quantity',
        'sales_starts_at',
        'sales_ends_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sales_starts_at' => 'datetime',
            'sales_ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function scopeAvailableForSale(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('is_active', true)
            ->where(function (Builder $query) use ($now): void{
                $query->whereNull('sales_starts_at')
                    ->orWhere('sales_starts_at', '<=', $now);
            })
            ->where(function (Builder $query) use ($now): void{
                $query->whereNull('sales_ends_at')
                    ->orWhere('sales_ends_at', '>', $now);
            });
    }
}