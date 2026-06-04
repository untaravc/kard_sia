# Release Notes v2.1

## Overview
Version 2.1 adds printable, PDF-ready documents for each student. Residents and admins can now generate a logbook, presence recap, and scoring report on demand, each accessible through a shareable link secured by the student's link token.

## What’s New
- **Print Student Logbook** (`/print/student-logbook`): Generates the student's logbook grouped per stase. Accepts a `link_token` query parameter to identify the student. Mandatory stases always appear, while non-mandatory stases are shown only when they contain logbook data.
- **Print Student Presences** (`/print/student-presences`): Produces a day-by-day presence recap for a chosen period. Accepts `link_token`, `start_date`, and `end_date` query parameters, listing daily check-in/check-out times and scientific agenda attendance.
- **Print Student Scores** (`/print/student-scores`): Generates the student's scoring report across all stase tasks. Accepts a `link_token` query parameter. Mandatory stases are listed first and always shown; non-mandatory stases without any score are omitted.

## Why it matters
These documents give students and supervisors a consistent, ready-to-print record of progress, attendance, and assessment, removing manual report assembly and making it easy to share results via a single link.

## Notes
Each document is reachable from the student dashboard under **Unduh Dokumen**, and from the student list actions in admin. Printing requires the student to have a generated `link_token`. If you have feedback or need rollout support, please share it with the product team.
