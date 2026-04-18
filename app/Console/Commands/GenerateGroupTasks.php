<?php

namespace App\Console\Commands;

use App\Services\GroupMatchingService;
use Illuminate\Console\Command;

class GenerateGroupTasks extends Command
{
    protected $signature   = 'app:generate-group-tasks';
    protected $description = 'Match university peers by schedule overlap and personality, then generate AI group tasks via Gemini';

    public function handle(GroupMatchingService $groupMatchingService): int
    {
        $this->info('Running group task matching...');

        $groupMatchingService->run();

        $this->info('Group task generation complete.');

        return self::SUCCESS;
    }
}
