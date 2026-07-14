<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\LogbookController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExportStudentLogbook extends Command
{
    protected $signature = 'logbook:export
                            {from_year : Tahun awal (inklusif)}
                            {to_year? : Tahun akhir (inklusif). Default sama dengan from_year}';

    protected $description = 'Generate & simpan PDF logbook untuk semua peserta didik pada rentang tahun tertentu';

    public function handle()
    {
        // Reduce noise from PHP 8.x deprecations in legacy dependencies.
        error_reporting(E_ERROR | E_PARSE);

        $fromYear = (string) $this->argument('from_year');
        $toYear = (string) ($this->argument('to_year') ?? $fromYear);

        $this->info("Exporting logbooks for year {$fromYear} - {$toYear} ...");

        $result = app(LogbookController::class)->generateStudentLogbooks(
            $fromYear,
            $toYear,
            function ($student, $path) {
                $this->line("  ✓ {$student->name} ({$student->year}) -> {$path}");
            }
        );

        if ($result['count'] === 0) {
            $this->warn('No students found in that year range. Nothing exported.');
            return 0;
        }

        $this->info("{$result['count']} logbook(s) saved to: " . Storage::path($result['directory']));

        return 0;
    }
}
