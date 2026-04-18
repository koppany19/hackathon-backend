<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subcategory extends Model
{
    protected $fillable = ['name', 'xp_value'];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
