<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'sport_frequency',
        'food',
        'sports',
        'social',
    ];

    protected $casts = [
        'food'   => 'array',
        'sports' => 'array',
        'social' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
