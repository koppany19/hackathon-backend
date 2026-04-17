<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'category',
        'subcategory',
        'xp_value',
        'is_active',
        'time',
        'location',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function dailyTasks(): HasMany
    {
        return $this->hasMany(DailyTask::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(CreatedTaskParticipant::class);
    }
}
