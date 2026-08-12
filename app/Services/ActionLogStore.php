<?php

namespace App\Services;

use Illuminate\Support\Carbon;

class ActionLogStore
{
    protected function directory()
    {
        return storage_path('logs/actions');
    }

    protected function pathForDate(Carbon $date)
    {
        return $this->directory() . '/' . $date->format('Ym') . '/' . $date->format('Y-m-d') . '.log';
    }

    public function write(array $entry)
    {
        $path = $this->pathForDate(now());
        $directory = dirname($path);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($path, json_encode($entry) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    public function readForDate($date)
    {
        try {
            $carbon = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (\Throwable $e) {
            return [];
        }

        $path = $this->pathForDate($carbon);
        if (!is_file($path)) {
            return [];
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        $entries = [];
        foreach ($lines as $line) {
            $decoded = json_decode($line, true);
            if (is_array($decoded)) {
                $entries[] = $decoded;
            }
        }

        return array_reverse($entries);
    }

    public function deleteOlderThan($days)
    {
        $threshold = now()->subDays($days)->startOfDay();
        $files = glob($this->directory() . '/*/*.log') ?: [];

        $deleted = 0;
        foreach ($files as $file) {
            $fileDate = Carbon::createFromFormat('Y-m-d', pathinfo($file, PATHINFO_FILENAME))->startOfDay();
            if ($fileDate->lt($threshold)) {
                unlink($file);
                $deleted++;
            }
        }

        foreach (glob($this->directory() . '/*', GLOB_ONLYDIR) ?: [] as $monthDirectory) {
            if (count(scandir($monthDirectory)) === 2) {
                rmdir($monthDirectory);
            }
        }

        return $deleted;
    }
}
