<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\RegistrationDetail;
use App\Models\RegistrationScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegistrationController extends Controller
{
    private const UNIV_TYPE = [
        ['name' => 'UGM', 'point' => 3.5],
        ['name' => 'UN-A-JAWA', 'point' => 3],
        ['name' => 'UN-A-LUAR', 'point' => 2.75],
        ['name' => 'UN-B-JAWA', 'point' => 2.5],
        ['name' => 'UN-B-LUAR', 'point' => 2.25],
        ['name' => 'UN-C-JAWA', 'point' => 2.25],
        ['name' => 'UN-C-LUAR', 'point' => 2],
        ['name' => 'US-A-JAWA', 'point' => 2.75],
        ['name' => 'US-A-LUAR', 'point' => 2.5],
        ['name' => 'US-B-JAWA', 'point' => 2.25],
        ['name' => 'US-B-LUAR', 'point' => 2],
        ['name' => 'US-C-JAWA', 'point' => 1.5],
        ['name' => 'US-C-LUAR', 'point' => 1.25],
    ];

    public function index(Request $request)
    {
        $query = Registration::query()
            ->with(['score', 'registration_details'])
            ->orderBy('name');

        if ($request->filled('keyword')) {
            $query->where('name', 'LIKE', '%' . $request->keyword . '%');
        }

        if ($request->filled('name')) {
            $query->where('name', 'LIKE', '%' . $request->name . '%');
        }

        if ($request->filled('email')) {
            $query->where('email', 'LIKE', '%' . $request->email . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('section')) {
            $section = strtolower((string) $request->section);
            switch ($section) {
                case 'journal':
                    $query->whereIn('status', [101, 200, 300, 400]);
                    break;
                case 'interview':
                    $query->whereIn('status', [300, 400]);
                    break;
                case 'administration':
                case 'administrasi':
                    $query->whereIn('status', [101, 200, 300, 400]);
                    break;
                case 'score':
                    $query->whereIn('status', [400]);
                    break;
            }
        }

        $period = env('REGISTRATION_PERIOD');
        if ($request->filled('registration_period')) {
            $query->where('registration_period', $request->registration_period);
        } elseif (!empty($period)) {
            $query->where('registration_period', $period);
        }

        $perPage = (int) ($request->per_page ?? 25);
        if ($perPage <= 0) {
            $perPage = 25;
        }

        $data = $query->paginate($perPage);

        $collection = $data->getCollection();
        if ($collection->count()) {
            $registrationIds = $collection->pluck('id')->values()->all();

            $englishByRegistrationId = RegistrationDetail::whereIn('registration_id', $registrationIds)
                ->where('name', 'like', 'Kemampuan bahasa inggris %')
                ->get()
                ->groupBy('registration_id');

            $tpaByRegistrationId = RegistrationDetail::whereIn('registration_id', $registrationIds)
                ->where('name', 'like', 'Tes Potensi %')
                ->get()
                ->groupBy('registration_id');

            $collection->transform(function ($item) use ($englishByRegistrationId, $tpaByRegistrationId) {
                $englishGroup = $englishByRegistrationId->get($item->id);
                $tpaGroup = $tpaByRegistrationId->get($item->id);

                $english = $englishGroup ? $englishGroup->first() : null;
                $tpa = $tpaGroup ? $tpaGroup->first() : null;

                if ($english) {
                    $english->setAttribute('name', str_replace('Kemampuan Bahasa Inggris ', '', $english->name));
                    $item->setAttribute('english', $english);
                }

                if ($tpa) {
                    $tpa->setAttribute('name', str_replace('Tes Potensi ', '', $tpa->name));
                    $item->setAttribute('tpa', $tpa);
                }

                $item->setAttribute('has_change', 0);
                return $item;
            });

            $data->setCollection($collection);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Registrations Success',
            'result' => $data,
        ]);
    }

    public function setScoreAdministration(Request $request)
    {
        $registrationId = $request->get('id');
        if (!$registrationId && $request->has('registration_id')) {
            $registrationId = $request->get('registration_id');
        }

        $registration = $registrationId ? Registration::find($registrationId) : null;
        if (!$registration) {
            return response()->json([
                'success' => false,
                'text' => 'Registration not found',
                'result' => null,
            ], 404);
        }

        $scorePayload = (array) $request->get('score', []);

        $selectionPathMultiplier = (float) ($scorePayload['selection_path_multiplier'] ?? 0);
        $pnsMultiplier = (float) ($scorePayload['pns_multiplier'] ?? 0);
        $originUniversityType = $scorePayload['origin_university_type'] ?? null;

        $originUniversityMultiplier = 0;
        if (!empty($originUniversityType)) {
            $type = collect(self::UNIV_TYPE)->firstWhere('name', $originUniversityType);
            if ($type) {
                $originUniversityMultiplier = (float) $type['point'];
            }
        }

        $qualityIpk = 0;
        if ($originUniversityMultiplier && $registration->ip_commulative) {
            $qualityIpk = $originUniversityMultiplier * (float) $registration->ip_commulative * 25;
            $qualityIpk = round($qualityIpk * 100) / 100;
        }

        $qualityToefl = (float) ($scorePayload['quality_toefl'] ?? 0);
        $qualityEnglish = 0;
        if ($qualityToefl > 0) {
            switch (true) {
                case $qualityToefl > 600:
                    $qualityEnglish = 95;
                    break;
                case $qualityToefl > 550:
                    $qualityEnglish = 90;
                    break;
                case $qualityToefl > 500:
                    $qualityEnglish = 85;
                    break;
                case $qualityToefl > 450:
                    $qualityEnglish = 80;
                    break;
                default:
                    $qualityEnglish = 75;
            }
        }

        $tpa = RegistrationDetail::where('name', 'like', 'Tes Potensi %')
            ->where('registration_id', $registration->id)
            ->first();

        $qualityTpa = 0;
        if ($tpa) {
            $tpaPoint = (float) $tpa->desc;
            switch (true) {
                case $tpaPoint > 700:
                    $qualityTpa = 100;
                    break;
                case $tpaPoint > 650:
                    $qualityTpa = 95;
                    break;
                case $tpaPoint > 600:
                    $qualityTpa = 90;
                    break;
                case $tpaPoint > 550:
                    $qualityTpa = 85;
                    break;
                default:
                    $qualityTpa = 80;
            }
        }

        $scoreWrittenExam = (float) ($scorePayload['score_written_exam'] ?? 0);
        $scoreEcg = (float) ($scorePayload['score_ecg'] ?? 0);

        $scoreWrittenExamTotal = 0;
        $qualityWrittenExam = 0;
        if ($scoreWrittenExam && $scoreEcg) {
            $scoreWrittenExamTotal = $scoreWrittenExam * 0.7 + $scoreEcg * 0.3;
            $qualityWrittenExam = $scoreWrittenExamTotal * 2;
        }

        $qualityJournal = (float) ($scorePayload['quality_journal'] ?? 0);

        $subtotalScore = 0;
        $subtotalScore += $selectionPathMultiplier;
        $subtotalScore += $pnsMultiplier;
        $subtotalScore += $qualityIpk;
        $subtotalScore += $qualityEnglish;
        $subtotalScore += $qualityTpa;
        $subtotalScore += $qualityWrittenExam;
        $subtotalScore += $qualityJournal;

        $registrationScore = RegistrationScore::where('registration_id', $registration->id)->first();
        $payload = [
            'selection_path_multiplier' => $selectionPathMultiplier,
            'pns_multiplier' => $pnsMultiplier,
            'origin_university_type' => $originUniversityType,
            'origin_university_multiplier' => $originUniversityMultiplier,
            'quality_ipk' => $qualityIpk,
            'quality_toefl' => $qualityToefl,
            'quality_english' => $qualityEnglish,
            'quality_tpa' => $qualityTpa,
            'score_written_exam' => $scoreWrittenExam,
            'score_ecg' => $scoreEcg,
            'score_written_exam_total' => $scoreWrittenExamTotal,
            'quality_written_exam' => $qualityWrittenExam,
            'subtotal_score' => $subtotalScore,
        ];

        if ($registrationScore) {
            $registrationScore->update($payload);
        } else {
            $payload['registration_id'] = $registration->id;
            $payload['period'] = $registration->registration_period;
            RegistrationScore::create($payload);
        }

        return response()->json([
            'success' => true,
            'text' => 'Update Registration Score Success',
            'result' => $payload,
        ]);
    }

    public function setScoreAdministrationAll(Request $request)
    {
        $query = Registration::query();
        if ($request->filled('registration_period')) {
            $query->where('registration_period', $request->registration_period);
        }

        $registrations = $query->get();
        foreach ($registrations as $registration) {
            $req = new Request();
            $req->merge(['id' => $registration->id, 'score' => []]);
            $this->setScoreAdministration($req);
        }

        return response()->json([
            'success' => true,
            'text' => 'Update Registration Score All Success',
            'result' => [
                'count' => $registrations->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $this->validateData($request);

        $payload = $request->all();
        if (!empty($payload['password'])) {
            $payload['password'] = Hash::make($payload['password']);
        }

        $registration = Registration::create($payload);

        return response()->json([
            'success' => true,
            'text' => 'Create Registration Success',
            'result' => $registration,
        ]);
    }

    public function show($id)
    {
        $registration = Registration::with(['details', 'score'])->find($id);
        if (!$registration) {
            return response()->json([
                'success' => false,
                'text' => 'Registration not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Registration Success',
            'result' => $registration,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $this->validate($request, [
            'status' => 'required|integer',
        ]);

        $registration = Registration::find($id);
        if (!$registration) {
            return response()->json([
                'success' => false,
                'text' => 'Registration not found',
                'result' => null,
            ], 404);
        }

        $registration->update([
            'status' => (int) $request->status,
        ]);

        return response()->json([
            'success' => true,
            'text' => 'Update Registration Status Success',
            'result' => $registration,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request, $id);

        $registration = Registration::find($id);
        if (!$registration) {
            return response()->json([
                'success' => false,
                'text' => 'Registration not found',
                'result' => null,
            ], 404);
        }

        $payload = $request->all();
        if (!empty($payload['password'])) {
            $payload['password'] = Hash::make($payload['password']);
        } else {
            unset($payload['password']);
        }

        $registration->update($payload);

        return response()->json([
            'success' => true,
            'text' => 'Update Registration Success',
            'result' => $registration,
        ]);
    }

    public function destroy($id)
    {
        $registration = Registration::find($id);
        if (!$registration) {
            return response()->json([
                'success' => false,
                'text' => 'Registration not found',
                'result' => null,
            ], 404);
        }

        $registration->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Registration Success',
            'result' => null,
        ]);
    }

    private function validateData(Request $request, $id = null)
    {
        $emailRule = 'required|email';
        if ($id) {
            $emailRule .= '|unique:registrations,email,' . $id;
        } else {
            $emailRule .= '|unique:registrations,email';
        }

        $this->validate($request, [
            'name' => 'required|string',
            'email' => $emailRule,
            'password' => $id ? 'nullable|string|min:6' : 'required|string|min:6',
        ]);
    }
}
