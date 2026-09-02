<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Legacy SIA -> BLU migration
    |--------------------------------------------------------------------------
    |
    | Newer student cohorts have been moved to the BLU application. When one of
    | them still lands on the old SIA host, RedirectSiaToBlu bounces them to the
    | BLU host (keeping the path + query string so deep links keep working).
    |
    | "legacy_host"  : bare host name of the old app (no scheme, no port).
    | "blu_url"      : full base URL of the new app.
    | "student_cohort_cutoff" : YYYY-MM. Students whose `year` is strictly
    |                  greater than this are treated as "already on BLU".
    | "preserve_path": append the current path + query to blu_url when true.
    |
    */

    'legacy_host' => env('LEGACY_HOST', 'sia.kardiologi-fkkmk.com'),

    'blu_url' => env('BLU_URL', 'https://blu.kardiologi-fkkmk.com'),

    'student_cohort_cutoff' => env('STUDENT_COHORT_CUTOFF', '2025-12'),

    'preserve_path' => (bool) env('LEGACY_REDIRECT_PRESERVE_PATH', true),

];
