<?php

namespace App\Console\Commands;

use App\Services\ActionLogStore;
use Illuminate\Console\Command;

class CleanActionLogs extends Command
{
    protected $signature = 'logs:clean-actions {--days=30 : Delete action log files older than this many days}';

    protected $description = 'Delete action log files (storage/logs/actions) older than the given number of days';

    public function handle(ActionLogStore $store)
    {
        $days = (int) $this->option('days');
        if ($days <= 0) {
            $days = 30;
        }

        $deleted = $store->deleteOlderThan($days);
        $this->info("Deleted {$deleted} action log file(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
