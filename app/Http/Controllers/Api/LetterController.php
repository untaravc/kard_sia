<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\WhatsAppController;
use App\Models\Lecture;
use App\Models\LectureProfile;
use App\Models\Letter;
use App\Models\LetterParticipant;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class LetterController extends Controller
{
    private function sanitizeEditorHtml($value)
    {
        if ($value === null) {
            return null;
        }

        $html = (string) $value;
        $html = preg_replace('/\x{FEFF}/u', '', $html);
        $html = preg_replace('/<span[^>]*class="ql-cursor"[^>]*>.*?<\/span>/si', '', $html);

        return $html;
    }

    private function romanMonth(int $month)
    {
        $map = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];

        return $map[$month] ?? (string) $month;
    }

    private function generateLetterNumber(\DateTimeInterface $createdAt)
    {
        $year = (int) $createdAt->format('Y');
        $month = (int) $createdAt->format('n');
        $order = Letter::whereYear('created_at', $year)->count() + 1;
        $orderMask = str_pad((string) $order, 3, '0', STR_PAD_LEFT);

        return $orderMask . '/E-Let/Kar.FKKMK/' . $this->romanMonth($month) . '/' . $year;
    }

    private function resolveAuthIdentity(Request $request)
    {
        $payload = $request->attributes->get('jwt_payload');
        $authType = $payload ? data_get($payload, 'log_as_auth_type') : null;
        if (!$authType) {
            $authType = $payload ? data_get($payload, 'auth_type') : null;
        }
        $authId = $payload ? data_get($payload, 'log_as_auth_id') : null;
        if (!$authId) {
            $authId = $payload ? data_get($payload, 'auth_id') : null;
        }

        return [$authType, $authId];
    }

    private function scopeQueryByViewer(Request $request, $query)
    {
        [$authType, $authId] = $this->resolveAuthIdentity($request);

        if (!$authType || !$authId) {
            return $query->whereRaw('1 = 0');
        }

        if ((string) $authType !== 'user') {
            $query->where('auth_type', $authType)
                ->where('auth_id', $authId);
        } else {
            if ($request->filled('auth_type')) {
                $query->where('auth_type', $request->auth_type);
            }
            if ($request->filled('auth_id')) {
                $query->where('auth_id', $request->auth_id);
            }
        }

        return $query;
    }

    private function canAccessLetter(Request $request, Letter $letter)
    {
        [$authType, $authId] = $this->resolveAuthIdentity($request);
        if ((string) $authType === 'user') {
            return true;
        }

        return (string) $letter->auth_type === (string) $authType && (string) $letter->auth_id === (string) $authId;
    }

    private function approvalBaseUrl()
    {
        $base = env('APP_URL');
        if (!$base) {
            $base = config('app.url');
        }

        return rtrim((string) $base, '/');
    }

    private function createPublishNotifications(Letter $letter)
    {
        $letter->loadMissing(['participants' => function ($query) {
            $query->orderBy('type')->orderBy('auth_type')->orderBy('auth_name');
        }]);

        $participants = $letter->participants ?: collect();
        if ($participants->isEmpty()) {
            return;
        }

        $baseUrl = $this->approvalBaseUrl();
        $letterName = $letter->title ?: ($letter->number ?: 'Surat');
        $letterDate = $letter->date ?: '-';
        $link = $baseUrl . '/letters/' . $letter->id . '/preview';

        foreach ($participants as $participant) {
            $authType = data_get($participant, 'auth_type');
            $authId = data_get($participant, 'auth_id');
            if (!$authType || !$authId) {
                continue;
            }

            Notification::create([
                'auth_type' => (string) $authType,
                'auth_id' => (int) $authId,
                'title' => 'Surat telah terbit',
                'content' => "Nama: {$letterName}\nTanggal: {$letterDate}",
                'link' => $link,
                'is_read' => 0,
            ]);
        }
    }

    public function index(Request $request)
    {
        $query = Letter::query()
            ->with(['participants' => function ($query) {
                $query->where('type', 'approval')
                    ->orderBy('auth_type')
                    ->orderBy('auth_name');
            }])
            ->orderByDesc('created_at');
        $query = $this->scopeQueryByViewer($request, $query);

        if ($request->filled('keyword')) {
            $keyword = (string) $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('number', 'LIKE', '%' . $keyword . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        $perPage = (int) ($request->per_page ?? 25);
        if ($perPage <= 0) {
            $perPage = 25;
        }

        $data = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Letters Success',
            'result' => $data,
        ]);
    }

    public function store(Request $request)
    {
        [$authType, $authId] = $this->resolveAuthIdentity($request);
        if (!$authType || !$authId) {
            return response()->json([
                'success' => false,
                'text' => 'Missing auth identity',
                'result' => null,
            ], 401);
        }

        $this->validate($request, [
            'number' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'intro' => 'nullable|string',
            'body' => 'nullable|string',
            'outro' => 'nullable|string',
            'attachment_content' => 'nullable|string',
            'attachment_label' => 'nullable|string|max:255',
            'custom_invitation' => 'nullable|string',
            'status' => 'nullable|integer',
            'study_program_code' => 'nullable|string|max:50',
            'participants' => 'nullable|array',
            'participants.*.auth_type' => 'required_with:participants|string|in:user,lecture,student',
            'participants.*.auth_id' => 'required_with:participants|integer',
            'participants.*.auth_name' => 'nullable|string|max:255',
            'participants.*.email' => 'nullable|string|max:255',
            'participants.*.phone' => 'nullable|string|max:255',
            'approval' => 'nullable|array',
            'approval.auth_type' => 'required_with:approval|string|in:user,lecture,student',
            'approval.auth_id' => 'required_with:approval|integer',
            'approval.auth_name' => 'nullable|string|max:255',
            'approval.email' => 'nullable|string|max:255',
            'approval.phone' => 'nullable|string|max:255',
            'approval.label' => 'nullable|string|max:255',
        ]);

        $now = now();
        $letter = Letter::create([
            'number' => $request->filled('number') ? trim((string) $request->number) : $this->generateLetterNumber($now),
            'auth_type' => $authType,
            'auth_id' => $authId,
            'date' => $request->date ?: $now->toDateString(),
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'intro' => $this->sanitizeEditorHtml($request->intro),
            'body' => $this->sanitizeEditorHtml($request->body),
            'outro' => $this->sanitizeEditorHtml($request->outro),
            'attachment_content' => $this->sanitizeEditorHtml($request->attachment_content),
            'attachment_label' => $request->attachment_label,
            'custom_invitation' => $this->sanitizeEditorHtml($request->custom_invitation),
            'status' => $request->status ?? 0,
            'token' => (string) Str::uuid(),
            'study_program_code' => $request->study_program_code,
        ]);

        $this->syncInviteParticipants($letter, $request->input('participants', []));
        $this->syncApprovalParticipant($letter, $request->input('approval'));
        if ((int) $letter->status === 1) {
            $this->createPublishNotifications($letter);
        }

        return response()->json([
            'success' => true,
            'text' => 'Create Letter Success',
            'result' => $letter->load('participants'),
        ]);
    }

    public function proposeApproval(Request $request, $id)
    {
        $letter = Letter::with(['participants' => function ($query) {
            $query->where('type', 'approval');
        }])->find($id);

        if (!$letter) {
            return response()->json([
                'success' => false,
                'text' => 'Letter not found',
                'result' => null,
            ], 404);
        }

        if (!$this->canAccessLetter($request, $letter)) {
            return response()->json([
                'success' => false,
                'text' => 'Forbidden',
                'result' => null,
            ], 403);
        }

        $approvals = $letter->participants ?: collect();
        if ($approvals->isEmpty()) {
            return response()->json([
                'success' => false,
                'text' => 'No approval participant found',
                'result' => null,
            ], 422);
        }

        if (!env('FONNTE_TOKEN')) {
            return response()->json([
                'success' => false,
                'text' => 'Missing FONNTE_TOKEN',
                'result' => null,
            ], 500);
        }

        $baseUrl = $this->approvalBaseUrl();
        $letterName = $letter->title ?: ($letter->number ?: 'Surat');
        $letterDate = $letter->date ?: '-';

        $lectureIds = $approvals
            ->where('auth_type', 'lecture')
            ->pluck('auth_id')
            ->filter()
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values()
            ->all();

        $lecturePhoneMap = [];
        if (!empty($lectureIds)) {
            $lecturePhoneMap = LectureProfile::whereIn('lecture_id', $lectureIds)
                ->pluck('phone', 'lecture_id')
                ->all();
        }

        $results = [];
        foreach ($approvals as $approval) {
            $name = $approval->auth_name ?: ($approval->auth_type . ' #' . $approval->auth_id);
            $phone = $approval->phone;
            if (!$phone && (string) $approval->auth_type === 'lecture') {
                $phone = $lecturePhoneMap[(int) $approval->auth_id] ?? null;
            }

            if (!$phone) {
                $results[] = [
                    'participant_id' => $approval->id,
                    'status' => 'skipped',
                    'reason' => 'Missing phone',
                ];
                continue;
            }

            $link = $baseUrl . '/letters/' . $letter->token . '/approval/' . $approval->token;
            $message = "Yth. {$name}\n\n"
                . "Surat menunggu persetujuan anda.\n"
                . "Nama: {$letterName}\n"
                . "Tanggal: {$letterDate}\n"
                . "Klik link berikut untuk menandatangani surat.\n\n"
                . $link;

            $error = app(WhatsAppController::class)->sendMessage($phone, $message);
            if ($error) {
                $results[] = [
                    'participant_id' => $approval->id,
                    'status' => 'failed',
                    'error' => $error,
                ];
                continue;
            }

            $results[] = [
                'participant_id' => $approval->id,
                'status' => 'sent',
                'phone' => $phone,
            ];
        }

        return response()->json([
            'success' => true,
            'text' => 'Propose approval sent',
            'result' => $results,
        ]);
    }

    public function notifyApprover(Request $request, $id)
    {
        $letter = Letter::with(['participants' => function ($query) {
            $query->where('type', 'approval');
        }])->find($id);

        if (!$letter) {
            return response()->json([
                'success' => false,
                'text' => 'Letter not found',
                'result' => null,
            ], 404);
        }

        if (!$this->canAccessLetter($request, $letter)) {
            return response()->json([
                'success' => false,
                'text' => 'Forbidden',
                'result' => null,
            ], 403);
        }

        if ((int) $letter->status !== 1) {
            return response()->json([
                'success' => false,
                'text' => 'Letter is not published',
                'result' => null,
            ], 422);
        }

        $approval = ($letter->participants ?: collect())->first();
        if (!$approval) {
            return response()->json([
                'success' => false,
                'text' => 'No approval participant found',
                'result' => null,
            ], 422);
        }

        if (!env('FONNTE_TOKEN')) {
            return response()->json([
                'success' => false,
                'text' => 'Missing FONNTE_TOKEN',
                'result' => null,
            ], 500);
        }

        $phone = $approval->phone;
        if (!$phone && (string) $approval->auth_type === 'lecture') {
            $phone = LectureProfile::where('lecture_id', (int) $approval->auth_id)->value('phone');
        }

        if (!$phone) {
            return response()->json([
                'success' => false,
                'text' => 'Missing phone',
                'result' => [
                    'participant_id' => $approval->id,
                    'status' => 'skipped',
                    'notify_at' => null,
                ],
            ], 422);
        }

        $baseUrl = $this->approvalBaseUrl();
        $letterName = $letter->title ?: ($letter->number ?: 'Surat');
        $letterDate = $letter->date ?: '-';
        $name = $approval->auth_name ?: ($approval->auth_type . ' #' . $approval->auth_id);
        $link = $baseUrl . '/letters/' . $letter->token;

        $message = "Yth. {$name}\n\n"
            . "Surat telah terbit.\n"
            . "Nama: {$letterName}\n"
            . "Tanggal: {$letterDate}\n"
            . "Klik link berikut untuk melihat surat.\n\n"
            . $link;

        $error = app(WhatsAppController::class)->sendMessage($phone, $message);
        if ($error) {
            return response()->json([
                'success' => false,
                'text' => 'Failed to send notification',
                'result' => [
                    'participant_id' => $approval->id,
                    'status' => 'failed',
                    'error' => $error,
                    'notify_at' => null,
                ],
            ], 500);
        }

        $approval->notify_at = now();
        $approval->save();

        return response()->json([
            'success' => true,
            'text' => 'Notify approver sent',
            'result' => [
                'participant_id' => $approval->id,
                'status' => 'sent',
                'phone' => $phone,
                'notify_at' => $approval->notify_at,
            ],
        ]);
    }

    public function cloneLetter(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required|string|max:255',
            'date' => 'nullable|date',
        ]);

        [$authType, $authId] = $this->resolveAuthIdentity($request);
        if (!$authType || !$authId) {
            return response()->json([
                'success' => false,
                'text' => 'Missing auth identity',
                'result' => null,
            ], 401);
        }

        $source = Letter::with(['participants' => function ($query) {
            $query->orderBy('type')->orderBy('auth_type')->orderBy('auth_name');
        }])->find($id);

        if (!$source) {
            return response()->json([
                'success' => false,
                'text' => 'Letter not found',
                'result' => null,
            ], 404);
        }

        if (!$this->canAccessLetter($request, $source)) {
            return response()->json([
                'success' => false,
                'text' => 'Forbidden',
                'result' => null,
            ], 403);
        }

        $now = now();
        $newLetter = null;

        DB::transaction(function () use ($request, $source, $authType, $authId, $now, &$newLetter) {
            $newLetter = Letter::create([
                'number' => $this->generateLetterNumber($now),
                'auth_type' => $authType,
                'auth_id' => $authId,
                'date' => $request->date ?: ($source->date ?: $now->toDateString()),
                'title' => $request->title,
                'subtitle' => $source->subtitle,
                'intro' => $source->intro,
                'body' => $source->body,
                'outro' => $source->outro,
                'attachment_content' => $source->attachment_content,
                'attachment_label' => $source->attachment_label,
                'custom_invitation' => $source->custom_invitation,
                'status' => 0,
                'token' => (string) Str::uuid(),
            ]);

            $participants = $source->participants ?: collect();
            foreach ($participants as $participant) {
                if (!$participant || !$participant->type) {
                    continue;
                }

                LetterParticipant::create([
                    'letter_id' => $newLetter->id,
                    'auth_type' => $participant->auth_type,
                    'auth_id' => $participant->auth_id,
                    'auth_name' => $participant->auth_name,
                    'auth_number' => $participant->auth_number,
                    'type' => $participant->type,
                    'label' => $participant->label,
                    'status' => 1,
                    'token' => (string) Str::uuid(),
                    'validated_at' => null,
                    'notify_at' => null,
                    'phone' => $participant->phone,
                    'email' => $participant->email,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'text' => 'Clone letter success',
            'result' => [
                'id' => $newLetter ? $newLetter->id : null,
            ],
        ]);
    }

    public function processApproval(Request $request, $letterToken, $letterParticipantToken)
    {
        $this->validate($request, [
            'status' => 'required|integer|in:1,2',
        ]);

        $status = (int) $request->status;

        $letter = Letter::where('token', $letterToken)->first();
        if (!$letter) {
            return response()->json([
                'success' => false,
                'text' => 'Letter not found',
                'result' => null,
            ], 404);
        }

        $approval = LetterParticipant::where('letter_id', $letter->id)
            ->where('type', 'approval')
            ->where('token', $letterParticipantToken)
            ->first();

        if (!$approval) {
            return response()->json([
                'success' => false,
                'text' => 'Approval participant not found',
                'result' => null,
            ], 404);
        }

        DB::transaction(function () use ($letter, $approval, $status) {
            $approval->update([
                'status' => $status,
                'validated_at' => now(),
            ]);

            $letter->update([
                'status' => $status,
            ]);
        });

        return response()->json([
            'success' => true,
            'text' => 'Approval processed',
            'result' => [
                'letter_status' => $letter->status,
                'participant_status' => $approval->status,
            ],
        ]);
    }

    public function show(Request $request, $id)
    {
        $letter = Letter::with(['participants' => function ($query) {
            $query->orderBy('type')->orderBy('auth_type')->orderBy('auth_name');
        }])->find($id);
        if (!$letter) {
            return response()->json([
                'success' => false,
                'text' => 'Letter not found',
                'result' => null,
            ], 404);
        }

        [$authType, $authId] = $this->resolveAuthIdentity($request);
        if ((string) $authType !== 'user') {
            if ((string) $letter->auth_type !== (string) $authType || (string) $letter->auth_id !== (string) $authId) {
                return response()->json([
                    'success' => false,
                    'text' => 'Forbidden',
                    'result' => null,
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Letter Success',
            'result' => $letter,
        ]);
    }

    public function update(Request $request, $id)
    {
        $letter = Letter::find($id);
        if (!$letter) {
            return response()->json([
                'success' => false,
                'text' => 'Letter not found',
                'result' => null,
            ], 404);
        }

        [$authType, $authId] = $this->resolveAuthIdentity($request);
        if ((string) $authType !== 'user') {
            if ((string) $letter->auth_type !== (string) $authType || (string) $letter->auth_id !== (string) $authId) {
                return response()->json([
                    'success' => false,
                    'text' => 'Forbidden',
                    'result' => null,
                ], 403);
            }
        }

        $wasPublished = (int) $letter->status === 1;

        $this->validate($request, [
            'number' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'intro' => 'nullable|string',
            'body' => 'nullable|string',
            'outro' => 'nullable|string',
            'attachment_content' => 'nullable|string',
            'attachment_label' => 'nullable|string|max:255',
            'custom_invitation' => 'nullable|string',
            'status' => 'nullable|integer',
            'study_program_code' => 'nullable|string|max:50',
            'participants' => 'nullable|array',
            'participants.*.auth_type' => 'required_with:participants|string|in:user,lecture,student',
            'participants.*.auth_id' => 'required_with:participants|integer',
            'participants.*.auth_name' => 'nullable|string|max:255',
            'participants.*.email' => 'nullable|string|max:255',
            'participants.*.phone' => 'nullable|string|max:255',
            'approval' => 'nullable|array',
            'approval.auth_type' => 'required_with:approval|string|in:user,lecture,student',
            'approval.auth_id' => 'required_with:approval|integer',
            'approval.auth_name' => 'nullable|string|max:255',
            'approval.email' => 'nullable|string|max:255',
            'approval.phone' => 'nullable|string|max:255',
            'approval.label' => 'nullable|string|max:255',
        ]);

        $letter->update([
            'number' => $request->filled('number') ? trim((string) $request->number) : $letter->number,
            'date' => $request->date,
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'intro' => $this->sanitizeEditorHtml($request->intro),
            'body' => $this->sanitizeEditorHtml($request->body),
            'outro' => $this->sanitizeEditorHtml($request->outro),
            'attachment_content' => $this->sanitizeEditorHtml($request->attachment_content),
            'attachment_label' => $request->attachment_label,
            'custom_invitation' => $this->sanitizeEditorHtml($request->custom_invitation),
            'status' => $request->status ?? $letter->status,
            'auth_type' => $letter->auth_type ?? $authType,
            'auth_id' => $letter->auth_id ?? $authId,
            'study_program_code' => $request->study_program_code ?? $letter->study_program_code,
        ]);

        if ($request->has('participants')) {
            $this->syncInviteParticipants($letter, $request->input('participants', []));
        }
        if ($request->has('approval')) {
            $this->syncApprovalParticipant($letter, $request->input('approval'));
        }

        $isPublished = (int) $letter->status === 1;
        if ($isPublished && (!$wasPublished || $request->has('participants') || $request->has('approval'))) {
            $this->createPublishNotifications($letter);
        }

        return response()->json([
            'success' => true,
            'text' => 'Update Letter Success',
            'result' => $letter->load('participants'),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $letter = Letter::find($id);
        if (!$letter) {
            return response()->json([
                'success' => false,
                'text' => 'Letter not found',
                'result' => null,
            ], 404);
        }

        [$authType, $authId] = $this->resolveAuthIdentity($request);
        if ((string) $authType !== 'user') {
            if ((string) $letter->auth_type !== (string) $authType || (string) $letter->auth_id !== (string) $authId) {
                return response()->json([
                    'success' => false,
                    'text' => 'Forbidden',
                    'result' => null,
                ], 403);
            }
        }

        $letter->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Letter Success',
            'result' => true,
        ]);
    }

    public function preview($id)
    {
        $letter = Letter::with(['participants' => function ($query) {
            $query->orderBy('type')->orderBy('auth_type')->orderBy('auth_name');
        }])->find($id);

        if (!$letter) {
            abort(404);
        }

        $approval = $letter->participants->firstWhere('type', 'approval');
        $invites = $letter->participants->where('type', 'invite')->values();

        return view('letters.standard', [
            'letter' => $letter,
            'approval' => $approval,
            'invites' => $invites,
        ]);
    }

    public function viesPublised($token)
    {
        $letter = Letter::with(['participants' => function ($query) {
            $query->orderBy('type')->orderBy('auth_type')->orderBy('auth_name');
        }])
            ->where('token', (string) $token)
            ->where('status', 1)
            ->first();

        if (!$letter) {
            abort(404);
        }

        $approval = $letter->participants->firstWhere('type', 'approval');
        $invites = $letter->participants->where('type', 'invite')->values();

        return view('letters.standard', [
            'letter' => $letter,
            'approval' => $approval,
            'invites' => $invites,
        ]);
    }

    public function approvalPage($letterToken, $approvalToken)
    {
        $letter = Letter::where('token', $letterToken)
            ->whereHas('participants', function ($query) use ($approvalToken) {
                $query->where('type', 'approval')->where('token', $approvalToken);
            })
            ->with(['participants' => function ($query) {
                $query->orderBy('type')->orderBy('auth_type')->orderBy('auth_name');
            }])
            ->first();

        if (!$letter) {
            abort(404);
        }

        $approval = $letter->participants
            ? $letter->participants->first(function ($participant) use ($approvalToken) {
                return (string) $participant->type === 'approval' && (string) $participant->token === (string) $approvalToken;
            })
            : null;

        if (!$approval) {
            abort(404);
        }

        $invites = $letter->participants ? $letter->participants->where('type', 'invite')->values() : collect();

        return view('letters.standard_approval', [
            'letter' => $letter,
            'approval' => $approval,
            'invites' => $invites,
        ]);
    }

    private function syncInviteParticipants(Letter $letter, $participants)
    {
        if (!is_array($participants)) {
            $participants = [];
        }

        $unique = [];
        foreach ($participants as $participant) {
            $authType = data_get($participant, 'auth_type');
            $authId = data_get($participant, 'auth_id');
            if (!$authType || !$authId) {
                continue;
            }
            $key = (string) $authType . ':' . (string) $authId;
            $unique[$key] = $participant;
        }

        $lectureIds = [];
        foreach ($unique as $participant) {
            if ((string) data_get($participant, 'auth_type') === 'lecture' && data_get($participant, 'auth_id')) {
                $lectureIds[] = (int) data_get($participant, 'auth_id');
            }
        }
        $lectureMap = [];
        if (!empty($lectureIds)) {
            $lectureMap = Lecture::whereIn('id', array_values(array_unique($lectureIds)))
                ->get(['id', 'name_alt', 'number', 'name'])
                ->keyBy('id')
                ->all();
        }

        $existing = LetterParticipant::withTrashed()
            ->where('letter_id', $letter->id)
            ->where('type', 'invite')
            ->get()
            ->keyBy(function ($participant) {
                return (string) $participant->auth_type . ':' . (string) $participant->auth_id;
            });

        foreach ($unique as $participant) {
            $authType = (string) data_get($participant, 'auth_type');
            $authId = data_get($participant, 'auth_id');
            $authName = data_get($participant, 'auth_name');
            $authNumber = null;

            if ($authType === 'lecture' && $authId) {
                $lecture = $lectureMap[(int) $authId] ?? null;
                if ($lecture) {
                    $authName = $lecture->name_alt ?: ($lecture->name ?: $authName);
                    $authNumber = $lecture->number ?: null;
                }
            }

            $key = $authType . ':' . (string) $authId;
            $existingParticipant = $existing->get($key);
            if ($existingParticipant) {
                if (method_exists($existingParticipant, 'trashed') && $existingParticipant->trashed()) {
                    $existingParticipant->restore();
                }
                $existingParticipant->update([
                    'auth_name' => $authName,
                    'auth_number' => $authNumber,
                    'label' => 'invite',
                    'phone' => data_get($participant, 'phone'),
                    'email' => data_get($participant, 'email'),
                ]);
                continue;
            }

            LetterParticipant::create([
                'letter_id' => $letter->id,
                'auth_type' => $authType,
                'auth_id' => $authId,
                'auth_name' => $authName,
                'auth_number' => $authNumber,
                'type' => 'invite',
                'label' => 'invite',
                'status' => 1,
                'token' => (string) Str::uuid(),
                'phone' => data_get($participant, 'phone'),
                'email' => data_get($participant, 'email'),
            ]);
        }

        $keepKeys = array_flip(array_keys($unique));
        LetterParticipant::where('letter_id', $letter->id)
            ->where('type', 'invite')
            ->get()
            ->each(function ($existingParticipant) use ($keepKeys) {
                $key = (string) $existingParticipant->auth_type . ':' . (string) $existingParticipant->auth_id;
                if (!isset($keepKeys[$key])) {
                    $existingParticipant->delete();
                }
            });
    }

    private function syncApprovalParticipant(Letter $letter, $approval)
    {
        if (!is_array($approval)) {
            return;
        }

        $authType = (string) data_get($approval, 'auth_type');
        $authId = data_get($approval, 'auth_id');
        if (!$authType || !$authId) {
            return;
        }

        $authName = data_get($approval, 'auth_name');
        $authNumber = null;
        if ($authType === 'lecture') {
            $lecture = Lecture::find($authId, ['id', 'name_alt', 'number', 'name']);
            if ($lecture) {
                $authName = $lecture->name_alt ?: ($lecture->name ?: $authName);
                $authNumber = $lecture->number ?: null;
            }
        }

        $existing = LetterParticipant::where('letter_id', $letter->id)
            ->where('type', 'approval')
            ->where('auth_type', $authType)
            ->where('auth_id', $authId)
            ->first();

        if ($existing) {
            $existing->update([
                'auth_name' => $authName,
                'auth_number' => $authNumber,
                'label' => data_get($approval, 'label') ?: ($existing->label ?: 'Kepala Departemen'),
                'phone' => data_get($approval, 'phone'),
                'email' => data_get($approval, 'email'),
            ]);
            return;
        }

        LetterParticipant::where('letter_id', $letter->id)
            ->where('type', 'approval')
            ->delete();

        LetterParticipant::create([
            'letter_id' => $letter->id,
            'auth_type' => $authType,
            'auth_id' => $authId,
            'auth_name' => $authName,
            'auth_number' => $authNumber,
            'type' => 'approval',
            'label' => data_get($approval, 'label') ?: 'Kepala Departemen',
            'status' => 1,
            'token' => (string) Str::uuid(),
            'phone' => data_get($approval, 'phone'),
            'email' => data_get($approval, 'email'),
        ]);
    }
}
