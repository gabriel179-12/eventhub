<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organizer extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo_path',
        'banner_path',
        'social_links',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organizer_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function events(): HasMany {
        return $this->hasMany(Event::class);
    }
    
}