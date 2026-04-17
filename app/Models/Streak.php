<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Streak extends Model
{
    protected $fillable = [
        'streak_count',
        'boost',
    ];

    protected $casts = [
        'boost' => 'integer',
    ];
}
