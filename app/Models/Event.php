<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'club_id',
        'title',
        'slug',
        'summary',
        'description',
        'location',
        'max_seats',
        'starts_at',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'max_seats' => 'integer',
            'starts_at' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function themes(): BelongsToMany
    {
        return $this->belongsToMany(Theme::class)->withTimestamps();
    }
}
