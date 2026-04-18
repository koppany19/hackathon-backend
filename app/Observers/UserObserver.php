<?php

namespace App\Observers;

use App\Models\User;
use App\Services\DailyTaskService;

class UserObserver
{
    public function created(User $user): void
    {
        app(DailyTaskService::class)->generateForUser($user);
    }
}
