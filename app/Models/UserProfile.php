<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'sport_frequency',
        'diet_type',
        'food_habits',
        'mental_health_score',
        'sleep_hours',
    ];

    protected $casts = [
        'food_habits' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
