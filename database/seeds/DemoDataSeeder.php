<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Generates a realistic-looking activity history for a hands-on demo:
 * 5 dedicated demo students who have taken every mandatory stase (with
 * ~80% of that stase's tasks scored, by one of 5 existing lecturers), plus
 * ~3 years of daily logbook entries (avg. 2/day) with random competency
 * skills attached.
 *
 * Uses dedicated "Demo Peserta N" student accounts (matched by a fixed
 * @kardio.demo email) rather than real seeded students, so this never
 * overwrites a real person's academic history and can be identified /
 * cleaned up later by that email pattern.
 *
 * Run with: php artisan db:seed --class=DemoDataSeeder
 */
class DemoDataSeeder extends Seeder
{
    private const STUDENT_EMAILS = [
        'demo.peserta1@kardio.demo',
        'demo.peserta2@kardio.demo',
        'demo.peserta3@kardio.demo',
        'demo.peserta4@kardio.demo',
        'demo.peserta5@kardio.demo',
    ];

    private const STUDENT_NAMES = [
        'Demo Peserta Satu',
        'Demo Peserta Dua',
        'Demo Peserta Tiga',
        'Demo Peserta Empat',
        'Demo Peserta Lima',
    ];

    private const SCORE_SYMBOLS = ['A', 'A-', 'B+', 'B', 'B-'];

    private const LOGBOOK_NOTES = [
        'Pasien stabil, terapi dilanjutkan.',
        'Follow up rutin, tidak ada keluhan baru.',
        'Kondisi membaik dibanding hari sebelumnya.',
        'Perlu pemeriksaan penunjang lanjutan.',
        'Konsul ke sejawat terkait.',
        'Edukasi pasien dan keluarga diberikan.',
    ];

    public function run()
    {
        $lectureIds = DB::table('lectures')->orderBy('id')->limit(5)->pluck('id')->all();
        $mandatoryStaseIds = DB::table('stases')->where('is_mandatory', 1)->orderBy('id')->pluck('id')->all();
        $competenceOptionIds = DB::table('form_options')
            ->where('type', 'sp1ipd-logbook-competence')
            ->pluck('id')
            ->all();

        if (empty($lectureIds)) {
            $this->info('No lectures found — run seedLectures (IpdDataSeederTmp) first. Aborting.');
            return;
        }
        if (empty($mandatoryStaseIds)) {
            $this->info('No mandatory stases found — run seedStases (IpdDataSeederTmp) first. Aborting.');
            return;
        }

        $staseTasksByStase = DB::table('stase_tasks')
            ->whereIn('stase_id', $mandatoryStaseIds)
            ->where('status', 1)
            ->get(['id', 'stase_id', 'task_id'])
            ->groupBy('stase_id');

        $windowStart = Carbon::today()->subDays(3 * 365);
        $windowEnd = Carbon::today();

        foreach (self::STUDENT_EMAILS as $index => $email) {
            $studentId = $this->ensureDemoStudent($email, self::STUDENT_NAMES[$index], $windowStart);

            if (DB::table('student_logs')->where('student_id', $studentId)->exists()) {
                $this->info("Skipping {$email} — already has demo history.");
                continue;
            }

            $this->info("Seeding history for {$email} ...");

            $schedule = $this->buildStaseSchedule($mandatoryStaseIds, $windowStart, $windowEnd);

            DB::transaction(function () use ($studentId, $schedule, $staseTasksByStase, $lectureIds) {
                $this->seedStaseHistory($studentId, $schedule, $staseTasksByStase, $lectureIds);
            });

            $this->seedLogbookHistory($studentId, $schedule, $windowStart, $windowEnd, $lectureIds, $competenceOptionIds);
        }

        $this->info('Done.');
    }

    /**
     * Creates the demo student if it doesn't exist yet (matched by email),
     * otherwise makes sure it's active. Returns the student id.
     */
    private function ensureDemoStudent(string $email, string $name, Carbon $intakeDate): int
    {
        $existingId = DB::table('students')->where('email', $email)->value('id');

        if ($existingId) {
            DB::table('students')->where('id', $existingId)->update([
                'status' => 'active',
                'updated_at' => now(),
            ]);

            return $existingId;
        }

        return DB::table('students')->insertGetId([
            'name' => $name,
            'email' => $email,
            'year' => $intakeDate->format('Y') . '-' . ($intakeDate->month <= 6 ? '01' : '07'),
            'status' => 'active',
            'password' => Hash::make('password'),
            'link_token' => Str::random(17),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Splits [$windowStart, $windowEnd] into one consecutive, non-overlapping
     * date range per mandatory stase (in id order), so every day of the
     * 3-year window belongs to exactly one "current stase" — used both to
     * create stase_logs and to tag logbook entries with a plausible stase_id.
     */
    private function buildStaseSchedule(array $mandatoryStaseIds, Carbon $windowStart, Carbon $windowEnd): array
    {
        $totalDays = $windowStart->diffInDays($windowEnd) + 1;
        $staseCount = count($mandatoryStaseIds);
        $daysPerStase = intdiv($totalDays, $staseCount);

        $schedule = [];
        $cursor = $windowStart->copy();

        foreach ($mandatoryStaseIds as $index => $staseId) {
            $isLast = $index === $staseCount - 1;
            $start = $cursor->copy();
            // The last stase absorbs the remainder days so the schedule
            // covers the full window with no trailing gap.
            $end = $isLast ? $windowEnd->copy() : $cursor->copy()->addDays($daysPerStase - 1);

            $schedule[] = [
                'stase_id' => $staseId,
                'start' => $start,
                'end' => $end,
            ];

            $cursor = $end->copy()->addDay();
        }

        return $schedule;
    }

    /**
     * Creates one stase_log per scheduled stase (status 'finish', since
     * these are all in the past), then scores ~80% of that stase's active
     * tasks with a random lecturer + score.
     */
    private function seedStaseHistory(int $studentId, array $schedule, $staseTasksByStase, array $lectureIds): void
    {
        $taskLogRows = [];

        foreach ($schedule as $entry) {
            $staseLogId = DB::table('stase_logs')->insertGetId([
                'student_id' => $studentId,
                'stase_id' => $entry['stase_id'],
                'start_date' => $entry['start']->format('Y-m-d 00:00:00'),
                'end_date' => $entry['end']->format('Y-m-d 23:59:59'),
                'status' => 'finish',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $tasks = $staseTasksByStase->get($entry['stase_id'], collect());

            foreach ($tasks as $task) {
                // Approx. 80% of tasks end up scored.
                if (random_int(1, 100) > 80) {
                    continue;
                }

                $scoreDate = $entry['start']->copy()->addDays(
                    random_int(0, max(0, $entry['start']->diffInDays($entry['end'])))
                );

                $taskLogRows[] = [
                    'task_id' => $task->task_id,
                    'stase_task_id' => $task->id,
                    'stase_id' => $entry['stase_id'],
                    'stase_log_id' => $staseLogId,
                    'student_id' => $studentId,
                    'lecture_id' => (string) $lectureIds[array_rand($lectureIds)],
                    'point_average' => random_int(70, 95),
                    'symbol' => self::SCORE_SYMBOLS[array_rand(self::SCORE_SYMBOLS)],
                    'date' => $scoreDate->format('Y-m-d H:i:s'),
                    'status' => 'publish',
                    'admin' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach (array_chunk($taskLogRows, 300) as $chunk) {
            DB::table('stase_task_logs')->insert($chunk);
        }
    }

    /**
     * Writes ~2 logbook entries/day (uniform 0-4, so the average lands on 2)
     * for every day of the window, each tagged with the stase the schedule
     * says the student was in that day, plus 1-3 random competency skills.
     */
    private function seedLogbookHistory(
        int $studentId,
        array $schedule,
        Carbon $windowStart,
        Carbon $windowEnd,
        array $lectureIds,
        array $competenceOptionIds
    ): void {
        $rows = [];
        $cursor = $windowStart->copy();
        $scheduleIndex = 0;

        while ($cursor->lte($windowEnd)) {
            while ($scheduleIndex < count($schedule) - 1 && $cursor->gt($schedule[$scheduleIndex]['end'])) {
                $scheduleIndex++;
            }
            $staseId = $schedule[$scheduleIndex]['stase_id'];

            $entriesToday = random_int(0, 4);
            for ($i = 0; $i < $entriesToday; $i++) {
                $category = array_rand(['rawat_inap' => 1, 'rawat_jalan' => 1, 'igd' => 1]);

                $rows[] = [
                    'student_id' => $studentId,
                    'lecture_id' => $lectureIds[array_rand($lectureIds)],
                    'type' => 'logbook-daily',
                    'stase_id' => $staseId,
                    'field_1' => 'RM-' . random_int(100000, 999999),
                    'field_2' => $category === 'rawat_inap' ? '1' : '0',
                    'field_3' => $category === 'rawat_jalan' ? '1' : '0',
                    'field_4' => $category === 'igd' ? '1' : '0',
                    'field_5' => self::LOGBOOK_NOTES[array_rand(self::LOGBOOK_NOTES)],
                    'date' => $cursor->format('Y-m-d'),
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (count($rows) >= 400) {
                $this->insertLogbookChunk($rows);
                $rows = [];
            }

            $cursor->addDay();
        }

        if (!empty($rows)) {
            $this->insertLogbookChunk($rows);
        }

        // These are brand-new demo students, so every logbook-daily row now
        // on file for them is exactly what this run just inserted.
        $logIds = DB::table('student_logs')
            ->where('student_id', $studentId)
            ->where('type', 'logbook-daily')
            ->pluck('id');

        $skillRows = [];
        foreach ($logIds as $logId) {
            if (empty($competenceOptionIds)) {
                break;
            }

            $skillCount = min(random_int(1, 3), count($competenceOptionIds));
            $picked = (array) array_rand($competenceOptionIds, $skillCount);

            foreach ($picked as $pickedIndex) {
                $skillRows[] = [
                    'student_id' => $studentId,
                    'student_log_id' => $logId,
                    'form_option_id' => $competenceOptionIds[$pickedIndex],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (count($skillRows) >= 400) {
                DB::table('student_log_skills')->insert($skillRows);
                $skillRows = [];
            }
        }

        if (!empty($skillRows)) {
            DB::table('student_log_skills')->insert($skillRows);
        }
    }

    private function insertLogbookChunk(array $rows): void
    {
        foreach (array_chunk($rows, 400) as $chunk) {
            DB::table('student_logs')->insert($chunk);
        }
    }

    private function info(string $message): void
    {
        if ($this->command) {
            $this->command->info($message);
            return;
        }

        fwrite(STDOUT, $message . PHP_EOL);
    }
}
