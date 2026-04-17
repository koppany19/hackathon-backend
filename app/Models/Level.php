<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $fillable = [
        'level',
        'needed_xp',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'level', 'level');
    }
}
