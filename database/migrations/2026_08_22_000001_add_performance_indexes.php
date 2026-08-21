<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds indexes to the columns the /api routes actually filter, join and sort
 * on. Most of the academic-record tables were created with nothing but a
 * PRIMARY KEY, so every monitoring / dashboard / scoring screen was driving
 * full table scans that grow linearly with the number of students.
 *
 * Each index below maps to a query pattern in app/Http/Controllers/Api;
 * composite column order follows the equality-first rule, with the leftmost
 * column also serving the single-column lookups on that table.
 *
 * Creation is guarded against missing tables/columns and pre-existing index
 * names, so the migration is safe to re-run and safe on databases that have
 * drifted from the migration history.
 */
class AddPerformanceIndexes extends Migration
{
    /**
     * table => list of column-sets to index.
     */
    private function indexes()
    {
        return [
            // Heaviest read table: student monitoring matrix, fulfilment
            // aggregates, per-stase detail, and the lecture scoring history.
            'stase_task_logs' => [
                ['student_id', 'stase_id'],
                ['stase_task_id'],
                ['lecture_id', 'date'],
            ],

            // Enrollment windows: looked up per student on nearly every
            // monitoring and scoring screen.
            'stase_logs' => [
                ['student_id', 'stase_id'],
                ['stase_id'],
            ],

            // Almost always fetched as "active tasks of this stase".
            'stase_tasks' => [
                ['stase_id', 'status'],
            ],

            // Logbook calendar scans a date range per student; the
            // lecture_id+status pair backs the pending-logbook nav badge that
            // runs on every lecture page load.
            'student_logs' => [
                ['student_id', 'date'],
                ['lecture_id', 'status'],
                ['stase_id'],
            ],

            // Eager-loaded per logbook entry, and aggregated per student for
            // the competency counters and the competence filter.
            'student_log_skills' => [
                ['student_log_id'],
                ['student_id', 'form_option_id'],
            ],

            // Score breakdown rows, joined back to task_details.
            'stase_task_log_points' => [
                ['stase_task_log_id'],
                ['task_detail_id'],
            ],

            // Presence monitoring filters by student over a checkin range.
            'presences' => [
                ['student_id', 'checkin'],
            ],

            'activity_students' => [
                ['student_id', 'activity_id'],
                ['activity_id'],
            ],

            'activity_lectures' => [
                ['lecture_id', 'activity_id'],
                ['activity_id'],
            ],

            // link_token backs the public scoring magic-link authentication.
            'open_stase_tasks' => [
                ['lecture_id'],
                ['stase_task_id'],
                ['link_token'],
            ],

            // Logbook types / skills / categories are resolved by type and by
            // owning stase on most logbook screens.
            'form_options' => [
                ['type', 'status'],
                ['relation_id', 'type'],
            ],

            'files' => [
                ['open_stase_task_id'],
                ['stase_task_log_id'],
            ],

            // setting() is resolved by label on effectively every request.
            'settings' => [
                ['label'],
            ],

            // Magic-link login and the public print/scoring links scan these
            // token columns on every attempt.
            'students' => [
                ['reset_password_token'],
                ['link_token'],
            ],
            'lectures' => [
                ['reset_password_token'],
                ['link_token'],
            ],
            'users' => [
                ['reset_password_token'],
            ],
        ];
    }

    public function up()
    {
        foreach ($this->indexes() as $table => $definitions) {
            foreach ($definitions as $columns) {
                $this->createIndex($table, $columns);
            }
        }
    }

    public function down()
    {
        foreach ($this->indexes() as $table => $definitions) {
            foreach ($definitions as $columns) {
                $this->dropIndex($table, $columns);
            }
        }
    }

    private function indexName($table, array $columns)
    {
        return $table . '_' . implode('_', $columns) . '_index';
    }

    private function createIndex($table, array $columns)
    {
        if (!$this->tableExists($table)) {
            return;
        }

        foreach ($columns as $column) {
            if (!$this->columnExists($table, $column)) {
                return;
            }
        }

        $name = $this->indexName($table, $columns);
        if ($this->indexExists($table, $name)) {
            return;
        }

        $columnList = '`' . implode('`, `', $columns) . '`';
        DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$name}` ({$columnList})");
    }

    private function dropIndex($table, array $columns)
    {
        if (!$this->tableExists($table)) {
            return;
        }

        $name = $this->indexName($table, $columns);
        if (!$this->indexExists($table, $name)) {
            return;
        }

        DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$name}`");
    }

    private function tableExists($table)
    {
        return (int) DB::selectOne(
            'SELECT COUNT(*) AS total FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = ?',
            [$table]
        )->total > 0;
    }

    private function columnExists($table, $column)
    {
        return (int) DB::selectOne(
            'SELECT COUNT(*) AS total FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
            [$table, $column]
        )->total > 0;
    }

    private function indexExists($table, $name)
    {
        return (int) DB::selectOne(
            'SELECT COUNT(*) AS total FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
            [$table, $name]
        )->total > 0;
    }
}
