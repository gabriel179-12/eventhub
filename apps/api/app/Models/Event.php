<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Event extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'starts_at',
        'ends_at',
        'venue_name',
        'address_line',
        'city',
        'state',
        'postal_code',
        'latitude',
        'longitude',
        'capacity',
        'is_private',
    ];

    protected function organizer(): BelongsTo {
        return $this->belongsTo(Organizer::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    
}
