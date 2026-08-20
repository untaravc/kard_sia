<?php

namespace App\Console\Commands;

use App\Models\StaseLog;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class NormalizeStaseLogDates extends Command
{
    protected $signature = 'stase-log:normalize-dates {--dry-run : Preview changes without saving}';

    protected $description = 'Fill missing stase_logs.start_date from created_at, and end_date from the related stase duration (in weeks)';

    public function handle()
    {
        // Reduce noise from PHP 8.x deprecations in legacy dependencies.
        error_reporting(E_ERROR | E_PARSE);

        $dryRun = (bool) $this->option('dry-run');

        $logs = StaseLog::with('stase')
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhereNull('end_date');
            })
            ->get();

        $filledStart = 0;
        $filledEnd = 0;
        $skipped = 0;

        foreach ($logs as $log) {
            $startDate = $log->getOriginal('start_date');
            $endDate = $log->getOriginal('end_date');
            $needsSave = false;

            if ($startDate === null) {
                if ($log->created_at === null) {
                    $this->warn("#{$log->id}: no created_at, cannot fill start_date, skipped.");
                    $skipped++;
                    continue;
                }
                $startDate = Carbon::parse($log->created_at);
                $needsSave = true;
                $filledStart++;
            } else {
                $startDate = Carbon::parse($startDate);
            }

            if ($endDate === null) {
                $weeks = $this->extractWeeks($log->stase->duration ?? null);
                if ($weeks !== null) {
                    $endDate = $startDate->copy()->addWeeks($weeks);
                    $needsSave = true;
                    $filledEnd++;
                } else {
                    $this->warn("#{$log->id}: stase_id={$log->stase_id} has no usable duration, end_date left null.");
                    $skipped++;
                }
            }

            if (!$needsSave) {
                continue;
            }

            $this->line(sprintf(
                '#%d stase_id=%s start_date=%s end_date=%s',
                $log->id,
                $log->stase_id ?? '-',
                $startDate->toDateTimeString(),
                $endDate instanceof Carbon ? $endDate->toDateTimeString() : '-'
            ));

            if (!$dryRun) {
                $log->start_date = $startDate;
                if ($endDate instanceof Carbon) {
                    $log->end_date = $endDate;
                }
                $log->save();
            }
        }

        $prefix = $dryRun ? '[DRY RUN] ' : '';
        $this->info("{$prefix}Done. start_date filled: {$filledStart}, end_date filled: {$filledEnd}, skipped: {$skipped}.");

        return 0;
    }

    private function extractWeeks(?string $duration): ?int
    {
        if ($duration === null || trim($duration) === '') {
            return null;
        }

        if (preg_match('/\d+/', $duration, $matches)) {
            return (int) $matches[0];
        }

        return null;
    }
}
