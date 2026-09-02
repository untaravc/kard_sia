# Features — Logged in as a Student (Resident / PPDS)

Scope: the modern SPA at **`/blu`** (`resources/js2`, Vue 2 + vue-router + Pinia,
built from `resources/js2/app.js` per `webpack.mix.js`). When the JWT identity is a
student, the app hides the admin sidebar and shows a **bottom navigation bar**
(`resources/js2/components/BottomNav.vue`) plus a slim top bar.

A short note on the legacy `/resident` app (old `resources/js`) is at the end.

---

## 1. How a student gets in

| Step | Detail |
|------|--------|
| Login page | `/blu/login` (`pages/auth/Login.vue`) — Email+Password, Email link, Phone link, or Google SSO |
| Login call | `POST /api/login` → `Api\AuthController@login`. Tries `user → lecture → student → registration`; a match on `students` issues a JWT with `auth_type = "student"`, `auth_id = <student id>` |
| Token | Stored in `localStorage.token`; `resources/js2/repository.js` adds `Authorization: Bearer <token>` to every request. `401` → wipe token + go to `/blu/login`; `404` → `/blu/not-found` |
| Passwordless | `POST /api/login-email` / `POST /api/login-phone` send a link; `POST /api/check-login-email-token` / `check-login-phone-token` exchange it for a JWT (phone match is via `student_profiles.phone`) |
| Admin "log as" | An admin calls `POST /api/log-as` `{auth_type:"student", auth_id}` → JWT with `log_as_auth_type = "student"`, `log_as_auth_id`. Every student endpoint reads `log_as_auth_type`/`log_as_auth_id` first, then `auth_type`/`auth_id`. Undo: `POST /api/logout-as` |
| Landing | After login the app routes to `/blu/dashboard`; `Topbar.handleAuthRedirect()` sees `auth_type === "student"` and forwards to `/blu/dashboard-student`, whose route **redirects to `/blu/dashboard-student/profile`** (`resources/js2/routes.js`) |
| Legacy login | `GET /resident-login` → `Auth\StudentLoginController` (guard `auth:student`), for the old `/resident` SPA |

**Layout** (`pages/Layout.vue`): `authType` from `GET /api/auth`, cached in `localStorage.auth_type`. For a student: `showSidebar = false`, `hasBottomNav = true`, mobile‑first padding with bottom room for the fixed nav.

---

## 2. Bottom menu

Rendered by `BottomNav.vue` → **`GET /api/menu?basePath=/blu`** → `Api\MenuController@menu`. Fixed list for `authType === "student"`. Active tab = current path equals or starts with the item's `to`.

| # | Label | Icon | Route | Badge / notes |
|---|-------|------|-------|---------------|
| 1 | **Scoring** | `mdi:clipboard-check-outline` | `/blu/dashboard-student/scoring` | — |
| 2 | **Agenda** | `mdi:calendar-month-outline` | `/blu/dashboard-student/agenda` | badge `counter` = count of `Activity` whose `start_date` is today (computed in `MenuController@menu`) |
| 3 | **Logbooks** | `mdi:notebook-outline` | `/blu/logbook-student-daily` **if** setting `app.version-logbook == 2`, else `/blu/logbook-student` | route chosen server‑side from `settings.app.version-logbook` |
| 4 | **Checklist** | `mdi:format-list-checks` | `/blu/dashboard-student/checklist` | — |
| 5 | **Profile** | `mdi:account-outline` | `/blu/dashboard-student/profile` | — |

`MenuController@menu` rejects (`401`) any `authType` not in `user / student / lecture`.

Routes that exist for students but are **not** in the bottom bar: `…/report` and `…/document` (placeholders), `…/scoring/:stase_log_id`, `…/scoring/confirm`, `/blu/logbook-student-add[/:id]`, `/blu/presences/student-daily`, plus shared pages such as `/blu/notifications`, `/blu/release-note`, `/blu/tutorial`, `/blu/activities`.

---

## 3. Pages

Format per page: **route → component**, what it shows, and each endpoint it calls (`METHOD /path` → `Controller@method` → behaviour). Student‑scoped endpoints resolve the student id from `log_as_auth_id` → `auth_id` (an admin may pass `?student_id=`).

### 3.1 Scoring (Stase) — `/blu/dashboard-student/scoring` → `pages/dashboard-student/Scoring.vue`

Two columns: **Available Stase** (searchable) and **Taken Stase** (searchable).

| Action | Endpoint | Behaviour |
|--------|----------|-----------|
| Load both lists | `GET /api/student-stase` → `StaseController@studentStase` | `taken_stase` = my `stase_logs` (newest first, with `stase`); `available_stase` = stases I have not taken |
| "Take Stase" modal | `POST /api/student-stase` → `StaseController@storeStudentStase` | body `{stase_id, start_date, end_date}` (`end_date >= start_date`); creates a `StaseLog` for me |
| "Edit" a taken stase | `PATCH /api/student-stase/{id}` → `StaseController@updateStudentStase` | body `{start_date, end_date}`; only my own `stase_log` |
| "Detail" | → `/blu/dashboard-student/scoring/:stase_log_id` | opens the task/scoring detail (3.2) |

### 3.2 Stase tasks & scoring detail — `/blu/dashboard-student/scoring/:stase_log_id` → `pages/dashboard-student/ScoringDetail.vue`

| Action | Endpoint | Behaviour |
|--------|----------|-----------|
| Resolve the stase | `GET /api/student-stase` | finds the `taken_stase` entry matching `:stase_log_id`, then… |
| Load tasks | `GET /api/student-stase-task/{stase_id}` → `StaseTaskController@studentStaseTask2` | active `stase_tasks` for the stase, each with my `openStaseTasks` (+ `files`, `lecture`) and my scored `staseTaskLogs` |
| Lecturer picker (in "Open Scoring") | `GET /api/lecture-list` → `LectureController@list` | active lecturers `{id, name}` |
| **Open Scoring** modal (create) | `POST /api/open-stase-task` → `OpenStaseTaskController@create` | `student`‑only; body `{stase_task_id, title, plan (date), lecture_ids[]}`; creates one `OpenStaseTask` per selected lecturer (each with a `link_token`) |
| Per open‑task **Actions** menu | | |
| • Konfirmasi Agenda | `POST /api/attendance-confirm` → `AttendanceCodeController@confirm` | `student`‑only; body `{open_stase_task_id, code (6 digits), method: "code"\|"qr", lat, lng}`. Verifies the code against **that task's lecturer's** rotating code; on success sets `validated_at / validated_method / validated_lat / validated_lng / validated_by`. If one code matches several pending tasks → `needs_selection` + `candidates` |
| • Notify Lecture → Email | `POST /api/open-stase-task/{id}/notify-email` → `OpenStaseTaskController@notifyEmail` | mails the lecturer a magic scoring link (`/blu/pub/scoring?llt=…&ostt=…`) |
| • Notify Lecture → WhatsApp | `POST /api/open-stase-task/{id}/notify-whatsapp` → `OpenStaseTaskController@notifyWhatsapp` | WhatsApp message with the same link (lecturer phone from `lecture_profiles.phone`) |
| • Upload Score / Upload Task | file → Firebase Storage `Student/ScoreDocument`, then `POST /api/files` → `FileController@create` | body `{title, desc, link, open_stase_task_id, stase_task_log_id, type: "score"\|"task"}` |
| • Update | `PATCH /api/open-stase-task/{id}` → `OpenStaseTaskController@update` | body `{title, plan}`; only my own; **disabled once a score exists** |
| • Delete | `DELETE /api/open-stase-task/{id}` → `OpenStaseTaskController@destroy` | soft‑delete; only my own; **disabled once a score exists** |
| Attachment preview | — | full‑screen modal (image or PDF) |

### 3.3 Confirm attendance (standalone) — `/blu/dashboard-student/scoring/confirm` → `pages/dashboard-student/ConfirmAttendance.vue`

Same `POST /api/attendance-confirm` as above. Reached by scanning a lecturer's QR (`?c=<code>` auto‑submits with `method: "qr"`) or by typing the 6 digits. States: **input** → (**select** if `needs_selection`) → **success**. Geolocation captured best‑effort (never blocks).

### 3.4 Agenda — `/blu/dashboard-student/agenda` → `pages/dashboard-student/Agenda.vue`

Date pager (‹ / ›, defaults today).

| Section | Endpoint | Behaviour |
|---------|----------|-----------|
| Schedule list | `GET /api/activities-today?date=` → `ActivityController@activitiesToday` | today's / selected day's `activities`, each annotated with my `absence` (`ActivityStudent` row) |
| Check‑in per row | `POST /api/activity-presence/{activity_id}` → `ActivityController@presence` | `firstOrCreate ActivityStudent(activity, me)`; a check‑in past the activity's `end_date` warns that the agenda creator is notified |

### 3.5 Logbooks

The bottom‑nav "Logbooks" tab points at one of the two below, chosen by setting `app.version-logbook`.

#### 3.5a v1 — `/blu/logbook-student` → `pages/logbooks/Student.vue`

Left rail = stase list; right = "Skill Achievements" + logbook entries for the selected stase.

| Action | Endpoint | Behaviour |
|--------|----------|-----------|
| Stase list | `GET /api/stase-list` → `StaseController@list` | my `stase_logs` (with `stase`) |
| Lecturer options | `GET /api/lecture-list` | for the "Supervisor" select |
| On stase select — options | `GET /api/stase-option/{stase_id}` → `LogbookController@staseOption` | `types` (`stase-logbook` form options) + `skills` (`logbook-skill`, each with my `count`) |
| On stase select — entries | `GET /api/student-logs/{stase_id}` → `LogbookController@studentLog` | entries grouped per logbook section (`parse_desc` drives the columns) + `categories` |
| "Add New" (per section) → bulk modal | `POST /api/logbooks/bulk` → `LogbookController@bulk` | body `{stase_id, type, category, lecture_id, date, data:[{field_1…field_6, skills{}}]}`; one `StudentLog` per row, `status = 0` (pending) |
| Row "Edit" | `PUT /api/logbooks/{id}` → `LogbookController@update` | rewrites `field_1…6`, `lecture_id`, `category`, `date`, `status = 0`, and replaces `StudentLogSkill` rows |
| Row "Delete" | `DELETE /api/logbooks/{id}` → `LogbookController@destroy` | — |
| "Print" | `GET /print/logbook/{studentId}/{staseId}` → `LogbookController@print` | PDF (Blade `templates.pdf.logbook`) |

#### 3.5b v2 — `/blu/logbook-student-daily` → `pages/logbooks/IndexV2.vue`

Daily‑log table (`type = logbook-daily`) + a "Kompetensi" panel.

| Action | Endpoint | Behaviour |
|--------|----------|-----------|
| List | `GET /api/logbooks?type=logbook-daily&date=&page=` → `LogbookController@index` | scoped to me; paginated 10; `field_1` shown as "No Catatan Medik" |
| Kompetensi panel | `GET /api/logbook-student-competence` → `LogbookController@competenceOptions` | `form_options` of type `sp1ipd-logbook-competence` with my per‑option `count` |
| Row "View" | — | modal: date, dosen, No Catatan Medik (`field_1`), patient category flags (`field_2/3/4` = Rawat Inap / Rawat Jalan Poli / IGD), note (`field_5`) |
| Row "Edit" | → `/blu/logbook-student-add/:id` | (3.5c in edit mode) |
| Row "Delete" | `DELETE /api/logbooks/{id}` → `LogbookController@destroy` | — |

#### 3.5c Add / Edit daily logbook — `/blu/logbook-student-add` and `/blu/logbook-student-add/:id` → `pages/logbooks/Add.vue`

Form: `date`, `no_catatan_medik`, checkboxes `rawat_inap / rawat_jalan / igd`, `note`, `lecture_id` (v‑select, `GET /api/lecture-list`), `competence_ids[]` (searchable, grouped; `GET /api/logbook-student-competence`).

| Action | Endpoint | Behaviour |
|--------|----------|-----------|
| Load (edit) | `GET /api/logbooks/{id}` → `LogbookController@show` | maps `field_1…5` + `stase_log_skills` back into the form |
| Create | `POST /api/logbook-student-add` → `LogbookController@storeDaily` | creates a `StudentLog` (`type = logbook-daily`, `status = 0`) + `StudentLogSkill` rows from `competence_ids` |
| Update | `PUT /api/logbook-student-add/{id}` → `LogbookController@updateDaily` | same shape; replaces skill rows |

### 3.6 Checklist — `/blu/dashboard-student/checklist` → `pages/dashboard-student/Checklist.vue`

`GET /api/student-checklist` → `StaseController@studentChecklist`. Returns:

- `summary` — `{done, total, percentage, stases}` across all my taken stases.
- `stases[]` — per taken stase: `done / total / percentage`, `ongoing` flag, and `tasks[]` where each task is either a scored `stase_task` (`done` if a `stase_task_log` with `point_average > 0` exists) or a prepended **Kehadiran** item (`type: "attendance"`, `present / working_days`, its own `percentage`) computed from my distinct check‑in days vs the stase's working days.

UI: overall progress bar; one collapsible row per stase (the ongoing one, or the first, expanded by default).

### 3.7 Profile — `/blu/dashboard-student/profile` → `pages/dashboard-student/Profile.vue` *(default landing)*

| Section | Endpoint | Behaviour |
|---------|----------|-----------|
| Profile card | `GET /api/student-profile` → `StudentController@profile` | `student` + `student_profiles` row; `student`‑only (403 otherwise) |
| "Edit Profile" modal | `PATCH /api/student-profile` → `StudentController@updateProfile` | fields `name, email, password (+ password_confirmation), phone, address, image`; photo uploaded to Firebase Storage `Student/Profile` |
| "Presensi Harian" card | `GET /api/student-presence-check` → `PresenceController@studentPresenceCheck` | my most recent `Presence` within 15 h; button label is **Check In** / **Check Out**; button → `/blu/presences/student-daily` (3.8) |
| "Unduh Dokumen" | (open in a new tab, need `student.link_token`) | **Penilaian** → `/print/student-scores?link_token=` (`ScoreController@printStudentScore`); **Logbook** → `/print/student-logbook?link_token=` (`LogbookController@printStudentLogbook`); **Presensi** → date‑range modal → `/print/student-presences?link_token=&start_date=&end_date=` (`PresenceController@printStudentPresence`) |
| Links | — | Release Notes → `/blu/release-note` (`GET /api/release-note`); Tutorial → `/documentations` (server page, `Home\DocumentationController@index`) |

### 3.8 Presensi Harian — `/blu/presences/student-daily` → `pages/presences/StudentDaily.vue`

Daily campus attendance with photo + geofence.

| Action | Endpoint | Behaviour |
|--------|----------|-----------|
| Load availability | `GET /api/student-daily-check` → `PresenceController@studentDailyCheck` | `available` (only on android / ios / linux platforms), `completed` (checkout already done today), `platform`, `student` (with `today_presence`) |
| Location | browser geolocation | distance from RSS (fixed `-7.7684412, 110.3721119`); a GPS accuracy ≥ 500 m is rejected with a "check here" map link |
| Photo | camera capture → client‑side WebP compression (≤ 1200 px, q 0.7) → Firebase Storage `Student/Presence` | optional |
| Submit | `POST /api/student-daily` → `PresenceController@studentDaily` | body `{note, status, photo_url, lat, lng, accuracy, distance}`. First submit of the day → **check‑in** (`status` = `on` "Masuk" / `off` "Izin" / `out` "Dinas Luar"); a later submit (> 10 min after check‑in) → **check‑out** ("Pulang"). Writes `checkin*/checkout*` columns on one `Presence` row per 15 h window |

### 3.9 Placeholders — `/blu/dashboard-student/report` and `…/document`

`pages/dashboard-student/Report.vue` / `Document.vue` — "Coming soon", no endpoints, not linked from any menu.

---

## 4. Cross‑cutting behaviour (top bar & app shell)

| Element | Endpoint(s) | Notes |
|---------|-------------|-------|
| Top bar | `GET /api/auth` → `AuthController@auth` | identity/claims; drives the `/dashboard → /dashboard-student` redirect. **No notification bell for students** (`Topbar.vue` shows it only for `authType === 'lecture'`) |
| ⋮ menu → **Logout** | `POST /api/logout` → `AuthController@logout` | clears `localStorage.token`, redirects `/blu/login` |
| **Logout As** (only if an admin is impersonating) | `POST /api/logout-as` → `AuthController@logoutAs` | swaps back to the admin JWT |
| Push registration | `initWebFcm()` (`firebase/messaging.js`) on load + after login; token via `Api\DeviceTokenController` | best‑effort |
| Menu | `GET /api/menu?basePath=/blu` → `MenuController@menu` | see section 2 |
| App name / login copy | `GET /api/settings/label/{label}` / `GET /api/app-config` | pre‑login |

All `/api/*` routes above sit behind the `jwt.auth` middleware group (`routes/api.php`). Endpoints such as `student-profile`, `student-daily*`, `open-stase-task` (create/destroy), `attendance-confirm` additionally assert `authType === 'student'` and return `403` otherwise. `401` anywhere → token cleared + redirect to `/blu/login`.

---

## 5. Legacy `/resident` app (old `resources/js` SPA) — brief

`routes/web-resident.php`, guard `auth:student`, session cookie (not JWT), API prefix `cmsr`. Controllers under `App\Http\Controllers\Resident\*`. Screens (Blade shell `resources/views/resident/layout.blade.php` + `sidebar.blade.php`, plus `presensi*.blade.php`; Vue under `resources/js/components/resident/*`):

- `resident` / `resident/{path}` → `Resident\DashboardController@index` (dashboard, `get-schedule`, `presence`, `info-cards`, `select-active-stase`, `add-stase`, `get-stase-log`)
- `cmsr/profiles` (+ `user`, `update_profile`), `cmsr/activities` (+ `init-activity`, `add-participants/{id}`)
- `cmsr/residents` (+ `upload-file`, `delete-file/{id}`), `cmsr/documents`, `document_categories`
- `cmsr/exams`, `cmsr/student-logs` (+ `student-logs-bulk`, `log-category/{id}`, `stase-list`)
- `cmsr/stase` (+ `stase_active`, `open-stase-task`, `close-stase-task`, `take-evaluation-stase/{stase_log_id}`, `stase-score/{id}`)
- Log book PDFs: `logbook-pdf/{id}`, `logbook-cover`, `compile-logbook-pdf`, `identity-pdf`
- Mirrored admin views: `student-score/{resident_id}`, `students/{resident_id}`, `resume-presences`
- `GET /e/{token}` → `Resident\ProctorshipController@exam` (proctored exam link)

This app is superseded by the `/blu` SPA above.
