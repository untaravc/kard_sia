# Features — Logged in as a Lecturer (Dosen)

Scope: the modern SPA at **`/blu`** (`resources/js2`, Vue 2 + vue-router + Pinia,
built from `resources/js2/app.js` per `webpack.mix.js`). When the JWT identity is a
lecturer, the app hides the admin sidebar and shows a **bottom navigation bar**
(`resources/js2/components/BottomNav.vue`) plus a slim top bar.

A short note on the legacy `/dosen` app (old `resources/js`) is at the end.

---

## 1. How a lecturer gets in

| Step | Detail |
|------|--------|
| Login page | `/blu/login` (`pages/auth/Login.vue`) — Email+Password, Email link, Phone link, or Google SSO |
| Login call | `POST /api/login` → `Api\AuthController@login`. Tries `user → lecture → student → registration`; a match on `lectures` issues a JWT with `auth_type = "lecture"`, `auth_id = <lecture id>` |
| Token | Stored in `localStorage.token`; `resources/js2/repository.js` adds `Authorization: Bearer <token>` to every request. `401` → wipe token + go to `/blu/login`; `404` → `/blu/not-found` |
| Passwordless | `POST /api/login-email` / `POST /api/login-phone` send a link; `POST /api/check-login-email-token` / `check-login-phone-token` exchange it for a JWT |
| SSO | `GET /api/login-google/redirect` → `…/callback` → redirect back to `/blu/login?token=…` |
| Admin "log as" | An admin calls `POST /api/log-as` `{auth_type:"lecture", auth_id}` → new JWT with `log_as_auth_type = "lecture"`, `log_as_auth_id`. Every lecturer endpoint reads `log_as_auth_type` first, then `auth_type`. Undo: `POST /api/logout-as` (shows a **Logout As** button in the top bar) |
| Landing | After login the app routes to `/blu/dashboard`; `Topbar.handleAuthRedirect()` sees `auth_type === "lecture"` and forwards to `/blu/dashboard-lecture`, whose route **redirects to `/blu/dashboard-lecture/profile`** (`resources/js2/routes.js`) |
| Legacy login | `GET /dosen-login` → `Auth\LectureLoginController` (guard `auth:lecture`), for the old `/dosen` SPA |

**Layout** (`pages/Layout.vue`): `authType` comes from `GET /api/auth` (`result.log_as_auth_type || result.auth_type`) and is cached in `localStorage.auth_type`. For a lecturer: `showSidebar = false`, `hasBottomNav = true`, content padding is mobile‑first with bottom room for the fixed nav.

---

## 2. Bottom menu

Rendered by `BottomNav.vue`, which calls **`GET /api/menu?basePath=/blu`** → `Api\MenuController@menu`. The controller returns a fixed list for `authType === "lecture"` (only falls back to hard‑coded *student* defaults if the fetch fails). Active tab = current path equals or starts with the item's `to`.

| # | Label | Icon | Route | Badge / notes |
|---|-------|------|-------|---------------|
| 1 | **Scoring** | `mdi:clipboard-check-outline` | `/blu/dashboard-lecture/scoring` | — |
| 2 | **Agenda** | `mdi:calendar-month-outline` | `/blu/dashboard-lecture/agenda` | — |
| 3 | **Logbook** | `mdi:notebook-outline` | `/blu/dashboard-lecture/logbook` | red badge = `StudentLog` where `lecture_id = me AND status = 0` (pending approvals), computed server‑side in `MenuController@menu` |
| 4 | **Profile** | `mdi:account-outline` | `/blu/dashboard-lecture/profile` | — |

`MenuController@menu` rejects (`401`) any `authType` not in `user / student / lecture`.

Routes that exist for lecturers but are **not** in the bottom bar: `…/report` (reached from Profile), `…/document` (placeholder, unlinked), plus shared pages such as `/blu/scores`, `/blu/notifications`, `/blu/release-note`, `/blu/tutorial`, `/blu/activities`.

---

## 3. Pages

Format per page: **route → component**, what it shows, and each endpoint it calls (`METHOD /path` → `Controller@method` → behaviour).

### 3.1 Scoring — `/blu/dashboard-lecture/scoring` → `pages/dashboard-lecture/Scoring.vue`

The lecturer's main work queue: assessment ("penilaian") requests raised by students.

| Section | Endpoint | Behaviour |
|---------|----------|-----------|
| Stat cards *Tunda / Selesai / Riwayat* | `GET /api/scoring-stat` → `ScoreController@stat` | `pending` = my `open_stase_tasks` (incl. `lecture_id = 0`) with no published `stase_task_log`; `done_this_month` = my `stase_task_logs` with `status = publish` in the last 30 days; `total` = all my `stase_task_logs` |
| "Penilaian" list (`ScoringCard.vue`) | `GET /api/open-stase-tasks?page=&keyword=&per_page=` → `OpenStaseTaskController@openStaseTask` | My requests (`lecture_id = me` **or** `0`); unscored first then scored; `LengthAwarePaginator`; keyword filter is debounced 350 ms; each row is joined to my matching `stase_task_log` as `data` |
| Attachment chips | — | open `FileDetailModal.vue` (inline image / PDF preview + download) |
| "Riwayat penilaian" link | → `/blu/scores` | shared score‑log browser (see 3.7) |
| "Nilai Langsung" quick‑score modal | `POST /api/lecture-add-score` → `StaseTaskLogController@lectureAddScore` | body `{stase_task_id, student_id, point_average, stase_id, task_id}`; `firstOrNew` `StaseTaskLog` for `(stase_log, stase_task, student, me)`, sets `point_average`, `admin = true`, `status = publish` |
| "Penilaian Ujian Umum" card (`ExamScoringCard.vue`, only if data) | `GET /cmsd/get-open-stase-task-all` → *legacy* `Lecture\HomeController@openStaseTaskAll` (`routes/web-dosen.php`) | general exam‑scoring rows shown on every lecturer dashboard |

**Row actions** depend on `item.stase_task.task.desc`:

| `desc` | Not yet scored | Already scored |
|--------|----------------|---------------|
| *(normal)* | **Nilai** → `/blu/task-scoring/:id` &nbsp;·&nbsp; **Nilai Langsung** (modal) | **Perbarui** → `/blu/task-scoring/:id` &nbsp;·&nbsp; **Perbarui Langsung** (modal, when `data.admin`) |
| `nilai-tesis` | **Nilai Thesis** → `/blu/task-scoring-thesis/:id` | **Perbarui Nilai Thesis** |
| `nilai-proposal` | **Nilai Proposal** → `/blu/task-scoring-proposal/:id` | **Perbarui Nilai Proposal** |

### 3.2 Scoring sheet — `/blu/task-scoring/:open_stase_task_id` → `pages/scores/TaskScore.vue`

| Action | Endpoint | Behaviour |
|--------|----------|-----------|
| Load the sheet | `GET /api/generate-task-log-detail?open_stase_task_id=` → `ScoreController@generateTaskLogDetail` | `lecture`‑only (403 otherwise). Returns the `stase_task_log` + ordered `stase_task_log_point` rows (joined to `task_details`). If I already have a log → returns it; else claims an unassigned log (sets `lecture_id = me`); else creates a fresh `StaseTaskLog` (`status = pending`) and one `StaseTaskLogPoint` per `TaskDetail` |
| Score entry | — | per point: a slider **78–100 step 2**, or **Ya/Tidak** for `type = bool`; two‑column layout; note textarea (a `chief` task shows a "Pertanyaan" textarea instead) |
| Submit | `POST /api/stase-task-logs-update-score/{stase_task_log_id}` → `ScoreController@staseTaskLogUpdate` | body `{params:{no:["<pointId>,<score>", …], note, open_stase_task_id}}`. Writes each point `score`, recomputes `point_amount / point_total / point_average`, sets `symbol` (A ≥95 / B ≥90 / C ≥85 / else E), copies `title` + `plan` from the open task, `status = publish`, `date = now`, and re‑parents the open task's files to this log. Redirects to `/blu/dashboard-lecture/scoring` |

**Thesis** (`pages/scores/TaskScoreThesis.vue`) → `POST /api/stase-task-logs-update-score-tesis/{id}` → `ScoreController@staseTaskLogUpdateTesis` — weights per `tesisScoreIndex(order, score)`, `point_average = round(total / 24)`, also stores `conclusion`.

**Proposal** (`pages/scores/TaskScoreProposal.vue`) → `POST /api/stase-task-logs-update-score-proposal/{id}` → `ScoreController@staseTaskLogUpdateProposal` — weights per `proposalScoreIndex(order, score)`, `point_average = round(total / 45)`, also stores `conclusion`.

### 3.3 Agenda — `/blu/dashboard-lecture/agenda` → `pages/activities/LectureActivities.vue`

Date pager (‹ / › around a date, defaults to today). "Semua agenda" → `/blu/activities`.

| Section | Endpoint | Behaviour |
|---------|----------|-----------|
| "Agenda Hari Ini" | `GET /api/activities-today?date=` → `ActivityController@activitiesToday` | today's (or selected day's) `activities`; each annotated with my `absence` (`ActivityLecture` row) when present |
| Check‑in per row | `POST /api/activity-presence/{activity_id}` → `ActivityController@presence` | `firstOrCreate ActivityLecture(activity, me)`; a check‑in past the activity's `end_date` warns that the agenda creator is notified |
| "Kegiatan Stase Hari Ini" | `GET /api/open-stase-tasks?date=&per_page=100` → `OpenStaseTaskController@openStaseTask` | my open assessments planned for that day; **Nilai** → `/blu/task-scoring/:id`, or shows the recorded `point_average` |

### 3.4 Logbook approval — `/blu/dashboard-lecture/logbook` → `pages/logbooks/LectureLogbook.vue`

Filters (persisted via the `persistFilters('dashboard-lecture/logbook')` mixin): keyword, stase, student, status (Pending / Approved / Rejected, default **Pending**).

| Action | Endpoint | Behaviour |
|--------|----------|-----------|
| Stase options | `GET /api/stase-list-all` → `StaseController@listAll` | all stases |
| Student options | `GET /api/student-list` → `StudentController@studentList` | active students (`id, name, year`) |
| Logbook list | `GET /api/logbooks?keyword=&stase_id=&student_id=&status=&type=&page=` → `LogbookController@index` | auto‑scoped to my `lecture_id` (`resolveLectureId`); paginated 10; each row carries a resolved `form_option_name` label and joined `student_name` |
| Row "View" | — | detail modal: stase, date, type, category, student, status, `field_1…field_6` |
| "Approve" (bulk) | `POST /api/logbooks/approve` → `LogbookController@approve` | body `{keyword, stase_id, student_id, status, type}`; sets `status = 1` on every matching non‑approved `StudentLog` scoped to my `lecture_id`; returns `{total, pending, updated}` |

### 3.5 PPDS Report — `/blu/dashboard-lecture/report` → `pages/dashboard-lecture/Report.vue`

Two link cards only (reached from the **Profile** page, not the bottom bar):

- **Daily** → `/blu/presences/daily` (`pages/presences/Daily.vue`) → `GET /api/presences/daily?date=` → `PresenceController@daily` — present vs not‑present students for a date.
- **Monthly** → `/blu/presences/monthly` (`pages/presences/Monthly.vue`) → `GET /api/presences/monthly?date=&key=` → `PresenceController@monthly` — per‑student monthly tally (`key` = `laporan jaga` or `presensi`).

### 3.6 Document — `/blu/dashboard-lecture/document` → `pages/dashboard-lecture/Document.vue`

Placeholder ("Coming soon"). No endpoints, not linked from any menu.

### 3.7 Profile — `/blu/dashboard-lecture/profile` → `pages/dashboard-lecture/Profile.vue` *(default landing)*

| Section | Endpoint | Behaviour |
|---------|----------|-----------|
| Profile card | `GET /api/lecture-profile` → `LectureController@profile` | `lecture` + `lecture_profiles` row; `lecture`‑only (403 otherwise) |
| "Perbarui Profil" modal (`ProfileModal.vue`) | `PATCH /api/lecture-profile` → `LectureController@updateProfile` | fields: `email, name, password (+ password_confirmation), phone, address, image`. The photo is uploaded to Firebase Storage under `Lecture/Profile` (`resources/js2/upload.js`), then the URL is sent; a base64 blob is processed server‑side instead |
| "Kode Kehadiran" (collapsible) | `GET /api/attendance-code` → `AttendanceCodeController@lectureCode` | returns a rotating **6‑digit code** + a **QR** (SVG data‑URI encoding a deep link) + `expires_in` / `period` (30 s, `AttendanceCodeService::PERIOD`). The client counts down and re‑fetches on expiry; students scan/enter it to confirm an assessment agenda (see the student doc) |
| Links | — | Release Notes → `/blu/release-note` (`GET /api/release-note` → `MarkdownController@releaseNote`); Tutorial → `/blu/tutorial` (`pages/posts/List.vue` → `GET /api/posts`); PPDS Report → `/blu/dashboard-lecture/report` |

### 3.8 Shared page reachable to lecturers — Scores — `/blu/scores` → `pages/scores/Index.vue`

`GET /api/stase-task-logs?keyword=&page=` → `StaseTaskLogController@index`. `scopedQuery()` scopes a lecturer to **their own** `stase_task_logs` (student name, stase/task, point average, status). Linked from the "Riwayat penilaian" text on the Scoring page.

---

## 4. Cross‑cutting behaviour (top bar & app shell)

| Element | Endpoint(s) | Notes |
|---------|-------------|-------|
| **Notification bell** (`Topbar.vue`, **lecturer only** — `showNotificationBell = authType === 'lecture'`) | `GET /api/web-notifications?limit=` · `POST /api/web-notifications/mark-all-read` · `POST /api/web-notifications/{id}/mark-read` → `WebNotificationController` (Firestore‑backed) | dropdown list + unread badge; "View all notifications" → `/blu/notifications` (`pages/notifications/Index.vue`, paged by 30) |
| Identity / redirect | `GET /api/auth` → `AuthController@auth` | returns raw JWT claims; drives `authType` and the `/dashboard → /dashboard-lecture` redirect |
| **Logout As** button (only when impersonating) | `POST /api/logout-as` → `AuthController@logoutAs` | swaps back to the admin JWT, reloads `/blu/dashboard` |
| ⋮ menu → **Logout** | `POST /api/logout` → `AuthController@logout` | clears `localStorage.token`, redirects `/blu/login` |
| Push registration | `initWebFcm()` (`resources/js2/firebase/messaging.js`) on app load + after login; device token stored via `Api\DeviceTokenController` (`device-tokens` resource) | best‑effort; errors ignored |
| Menu | `GET /api/menu?basePath=/blu` → `MenuController@menu` | see section 2 |

All `/api/*` routes above sit behind the `jwt.auth` middleware group (`routes/api.php`). Endpoints that mutate scores or read `attendance-code` additionally assert `authType === 'lecture'` and return `403` otherwise.

---

## 5. Legacy `/dosen` app (old `resources/js` SPA) — brief

`routes/web-dosen.php`, guard `auth:lecture`, session cookie (not JWT), API prefix `cmsd`. Controllers under `App\Http\Controllers\Lecture\*`. Screens (Blade shell `resources/views/lecture/layout.blade.php` + `sidebar.blade.php`, Vue under `resources/js/components/lecture/*`):

- `dosen` / `dosen/{path}` → `Lecture\HomeController@index` (dashboard, `history`, `get-schedule`, `presence`, `get-open-stase-task[-all]`)
- `cmsd/profiles`, `cmsd/user`, `update_password`, `update_profile`
- `cmsd/activities`, `cmsd/documents`, `cmsd/ksm-schedules`, `cmsd/student-logs`
- `cmsd/resident-log-list` (+ `/{id}` GET/POST accept), `cmsd/stase-task-log/jurnal/{id}`
- Scoring functions: `stase-task-logs-update-score[...-tesis|-proposal]/{id}`, `generate-task-log-detail`
- Mirrored admin views: `cmsd/students`, `cmsd/task-details`, `student-score/{resident_id}`, `dashboard-stase`, `presences`, `presences_today`, `resume-presences`, `stase-plots`, `get-stases`, `document_categories`

This app is superseded by the `/blu` SPA above; only `cmsd/get-open-stase-task-all` is still consumed by the new Scoring page.
