<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Bounce post-cutoff student cohorts from the legacy SIA host to BLU.
 *
 * Fires only when ALL of these hold:
 *   1. the request came in on config('domain.legacy_host'); and
 *   2. a student-guard user is authenticated; and
 *   3. that student's `year` (cohort, "YYYY-MM") is after
 *      config('domain.student_cohort_cutoff').
 *
 * Anyone else (guests, lecturers, admins, older student cohorts) passes
 * through untouched. Must run AFTER StartSession so the student guard is
 * resolvable — it is registered inside the `web` middleware group.
 */
class RedirectSiaToBlu
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $legacyHost = config('domain.legacy_host');
        $bluUrl     = rtrim((string) config('domain.blu_url'), '/');

        // Not the old host, or BLU target is misconfigured / same host -> skip
        // (the same-host guard also prevents a redirect loop).
        if (
            ! $legacyHost
            || ! $bluUrl
            || strcasecmp($request->getHost(), $legacyHost) !== 0
            || strcasecmp($request->getHost(), (string) parse_url($bluUrl, PHP_URL_HOST)) === 0
        ) {
            return $next($request);
        }

        $student = Auth::guard('student')->user();

        if ($student && $this->cohortIsAfterCutoff(
            $student->year,
            (string) config('domain.student_cohort_cutoff')
        )) {
            $target = $bluUrl;

            if (config('domain.preserve_path', true)) {
                $target .= '/' . ltrim($request->getRequestUri(), '/');
            }

            return redirect()->away($target, 302);
        }

        return $next($request);
    }

    /**
     * Is a "YYYY-MM" cohort string strictly later than the cutoff?
     *
     * Tolerant of loose stored values ("2026", "2026-1", "2026/01",
     * "2026-01-15"); anything unparseable is treated as "not after" so an
     * unknown cohort is never bounced.
     *
     * @param  string|null  $year
     * @param  string  $cutoff
     * @return bool
     */
    protected function cohortIsAfterCutoff($year, $cutoff)
    {
        $cohortNum = $this->toYearMonthInt($year);
        $cutoffNum = $this->toYearMonthInt($cutoff);

        if ($cohortNum === null || $cutoffNum === null) {
            return false;
        }

        return $cohortNum > $cutoffNum;
    }

    /**
     * Normalise a date-ish string to an int in YYYYMM form, or null.
     *
     * @param  string|null  $value
     * @return int|null
     */
    protected function toYearMonthInt($value)
    {
        $digits = preg_replace('/\D/', '', (string) $value);

        if (strlen($digits) < 4) {
            return null;
        }

        $yyyy = substr($digits, 0, 4);
        $mm   = strlen($digits) >= 6 ? substr($digits, 4, 2) : '00';

        return (int) ($yyyy . $mm);
    }
}
