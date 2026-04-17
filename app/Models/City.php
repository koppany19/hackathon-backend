<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = [
        'name',
    ];

    public function universities(): HasMany
    {
        return $this->hasMany(University::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
