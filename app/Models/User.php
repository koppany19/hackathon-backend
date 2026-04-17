<?php

namespace App\Models;
use App\Models\City;
use App\Models\University;
use App\Models\UserProfile;
use App\Models\DailyTask;
use App\Models\CreatedTaskParticipant;
use App\Models\ScheduleItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'university_id',
        'city_id',
        'xp',
        'profile_image',
        'level',
        'streak'
    ];

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function dailyTasks(): HasMany
    {
        return $this->hasMany(DailyTask::class);
    }

    public function taskParticipations(): HasMany
    {
        return $this->hasMany(CreatedTaskParticipant::class);
    }

    public function scheduleItems(): HasMany
    {
        return $this->hasMany(ScheduleItem::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'level', 'level');
    }

    public function nextLevel(): HasOne
    {
        return $this->hasOne(Level::class, 'level', 'level')
            ->where('level', $this->level + 1);
    }

    public function streakLevel(): BelongsTo
    {
        return $this->belongsTo(Streak::class, 'streak', 'streak_count');
    }

    public function getCurrentBoost(): int
    {
        return $this->streakLevel?->boost ?? 0;
    }
}
