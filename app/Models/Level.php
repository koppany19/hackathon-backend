<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
