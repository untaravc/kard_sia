<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Presence;

trait CalculatesAttendance
{
    /**
     * Distinct check-in days ('Y-m-d') of a student, for attendance counting.
     */
    protected function studentPresenceDates($studentId)
    {
        return Presence::whereStudentId($studentId)
            ->whereNotNull('checkin')
            ->pluck('checkin')
            ->map(function ($checkin) {
                return substr($checkin, 0, 10);
            })
            ->unique()
            ->values();
    }

    /**
     * Attendance (Kehadiran) item for a stase's date range.
     * Compares distinct check-in days against the weekdays (Mon-Fri) in range,
     * capped at today so future days of an ongoing stase are not counted.
     * A shortfall under the 5% tolerance is treated as full (100%) attendance.
     *
     * @param  string|null  $start  'Y-m-d'
     * @param  string|null  $end    'Y-m-d'
     * @param  string       $today  'Y-m-d'
     * @param  \Illuminate\Support\Collection  $presenceDates  distinct 'Y-m-d' strings
     * @return array|null
     */
    protected function attendanceItem($start, $end, $today, $presenceDates)
    {
        if (!$start || !$end) {
            return null;
        }

        $effectiveEnd = $end < $today ? $end : $today;
        if ($effectiveEnd < $start) {
            return null;
        }

        $workingDays = $this->countWeekdays($start, $effectiveEnd);
        if ($workingDays <= 0) {
            return null;
        }

        $present = $presenceDates->filter(function ($date) use ($start, $effectiveEnd) {
            return $date >= $start && $date <= $effectiveEnd;
        })->count();

        $present = min($present, $workingDays);
        $absentRate = ($workingDays - $present) / $workingDays;

        $percentage = $absentRate < 0.05
            ? 100
            : (int) round($present / $workingDays * 100);

        return [
            'stase_task_id' => null,
            'type' => 'attendance',
            'name' => 'Kehadiran',
            'done' => $percentage >= 100,
            'point_average' => null,
            'percentage' => $percentage,
            'present' => $present,
            'working_days' => $workingDays,
            'date' => null,
        ];
    }

    /**
     * Count weekdays (Mon-Fri) between two 'Y-m-d' dates, inclusive.
     */
    protected function countWeekdays($start, $end)
    {
        $count = 0;
        $cursor = strtotime($start);
        $limit = strtotime($end);

        while ($cursor <= $limit) {
            if ((int) date('N', $cursor) < 6) {
                $count++;
            }
            $cursor = strtotime('+1 day', $cursor);
        }

        return $count;
    }
}
