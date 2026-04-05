<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Letter;
use App\Models\LetterParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LetterController extends Controller
{
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

        return $orderMask . '/ED/Kar/' . $this->romanMonth($month) . '/' . $year;
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

    public function index(Request $request)
    {
        $query = Letter::query()->orderByDesc('created_at');
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
            'date' => 'nullable|date',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'intro' => 'nullable|string',
            'body' => 'nullable|string',
            'outro' => 'nullable|string',
            'status' => 'nullable|integer',
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
            'number' => $this->generateLetterNumber($now),
            'auth_type' => $authType,
            'auth_id' => $authId,
            'date' => $request->date ?: $now->toDateString(),
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'intro' => $request->intro,
            'body' => $request->body,
            'outro' => $request->outro,
            'status' => $request->status ?? 0,
            'token' => (string) Str::uuid(),
        ]);

        $this->syncInviteParticipants($letter, $request->input('participants', []));
        $this->syncApprovalParticipant($letter, $request->input('approval'));

        return response()->json([
            'success' => true,
            'text' => 'Create Letter Success',
            'result' => $letter->load('participants'),
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

        $this->validate($request, [
            'date' => 'nullable|date',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'intro' => 'nullable|string',
            'body' => 'nullable|string',
            'outro' => 'nullable|string',
            'status' => 'nullable|integer',
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
            'date' => $request->date,
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'intro' => $request->intro,
            'body' => $request->body,
            'outro' => $request->outro,
            'status' => $request->status ?? $letter->status,
            'auth_type' => $letter->auth_type ?? $authType,
            'auth_id' => $letter->auth_id ?? $authId,
        ]);

        if ($request->has('participants')) {
            $this->syncInviteParticipants($letter, $request->input('participants', []));
        }
        if ($request->has('approval')) {
            $this->syncApprovalParticipant($letter, $request->input('approval'));
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

        return view('letters.standar', [
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

        LetterParticipant::where('letter_id', $letter->id)
            ->where('type', 'invite')
            ->delete();

        foreach ($unique as $participant) {
            LetterParticipant::create([
                'letter_id' => $letter->id,
                'auth_type' => data_get($participant, 'auth_type'),
                'auth_id' => data_get($participant, 'auth_id'),
                'auth_name' => data_get($participant, 'auth_name'),
                'type' => 'invite',
                'label' => 'invite',
                'status' => 1,
                'token' => (string) Str::uuid(),
                'phone' => data_get($participant, 'phone'),
                'email' => data_get($participant, 'email'),
            ]);
        }
    }

    private function syncApprovalParticipant(Letter $letter, $approval)
    {
        LetterParticipant::where('letter_id', $letter->id)
            ->where('type', 'approval')
            ->delete();

        if (!is_array($approval)) {
            return;
        }

        $authType = data_get($approval, 'auth_type');
        $authId = data_get($approval, 'auth_id');
        if (!$authType || !$authId) {
            return;
        }

        LetterParticipant::create([
            'letter_id' => $letter->id,
            'auth_type' => $authType,
            'auth_id' => $authId,
            'auth_name' => data_get($approval, 'auth_name'),
            'type' => 'approval',
            'label' => data_get($approval, 'label') ?: 'Kepala Departemen',
            'status' => 1,
            'token' => (string) Str::uuid(),
            'phone' => data_get($approval, 'phone'),
            'email' => data_get($approval, 'email'),
        ]);
    }
}
