<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleItem extends Model
{
    protected $fillable = [
        'user_id',
        'day_of_week',
        'subject_name',
        'start_time',
        'end_time',
    ];

    protected $visible = [
        'id',
        'user_id',
        'day_of_week',
        'subject_name',
        'start_time',
        'end_time',
        'created_at',
        'updated_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
