<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FormOption;
use App\Models\Stase;
use App\Models\StaseLog;
use App\Models\Student;
use App\Models\StudentLog;
use App\Models\StudentProfile;
use App\Models\StudentLogSkill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade as PDF;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class LogbookController extends Controller
{
    public function approve(Request $request)
    {
        $lectureId = $this->resolveLectureId($request);
        if ($lectureId) {
            $request->merge([
                'lecture_id' => $lectureId,
            ]);
        }

        $query = StudentLog::query();
        $query = $this->withApproveFilter($query, $request);

        $total = (clone $query)->count();
        $pending = (clone $query)->where('status', '!=', 1)->count();
        $updated = $pending ? (clone $query)->where('status', '!=', 1)->update(['status' => 1]) : 0;

        return response()->json([
            'success' => true,
            'text' => 'Approve Logbooks Success',
            'result' => [
                'total' => $total,
                'pending' => $pending,
                'updated' => $updated,
            ],
        ]);
    }

    public function index(Request $request)
    {
        $studentId = $this->resolveStudentId($request);
        $lectureId = $this->resolveLectureId($request);
        $dataContent = StudentLog::query()
            ->select('student_logs.*', 'form_options.name as form_option_name', 'students.name as student_name')
            ->leftJoin('form_options', function ($join) {
                $join->on('form_options.value', '=', 'student_logs.type')
                    ->where('form_options.type', '=', 'stase-logbook')
                    ->whereColumn('form_options.relation_id', '=', 'student_logs.stase_id');
            })
            ->leftJoin('students', 'students.id', '=', 'student_logs.student_id')
            ->with(['lecture', 'stase'])
            ->when($studentId, function ($query) use ($studentId) {
                $query->where('student_id', $studentId);
            })
            ->when($lectureId, function ($query) use ($lectureId) {
                $query->where('student_logs.lecture_id', $lectureId);
            })
            ->orderByDesc('student_logs.date');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Logbooks Success',
            'result' => $dataContent,
        ]);
    }

    public function bulk(Request $request)
    {
        $studentId = $this->resolveStudentId($request);

        $request->merge([
            'student_id' => $studentId,
            'status' => 0,
            'date' => $request->date ?? date('Y-m-d'),
        ]);

        $this->validateData($request);

        if (is_array($request->data) && count($request->data) > 0) {
            foreach ($request->data as $datum) {
                StudentLog::create([
                    'student_id' => $request->student_id,
                    'status' => $request->status,
                    'stase_id' => $request->stase_id,
                    'type' => $request->type,
                    'lecture_id' => $request->lecture_id,
                    'date' => $request->date,
                    'category' => $request->category,
                    'field_1' => $datum['field_1'] ?? null,
                    'field_2' => $datum['field_2'] ?? null,
                    'field_3' => $datum['field_3'] ?? null,
                    'field_4' => $datum['field_4'] ?? null,
                    'field_5' => $datum['field_5'] ?? null,
                    'field_6' => $datum['field_6'] ?? null,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'text' => 'Bulk create logbook success',
            'result' => null,
        ]);
    }

    public function storeDaily(Request $request)
    {
        $studentId = $this->resolveStudentId($request);

        $this->validate($request, [
            'date' => 'required|date',
            'no_catatan_medik' => 'required|string',
            'rawat_inap' => 'nullable|string',
            'rawat_jalan' => 'nullable|string',
            'lecture_id' => 'nullable|integer',
            'competence_ids' => 'nullable|array',
            'competence_ids.*' => 'integer',
        ]);

        $logbook = StudentLog::create([
            'student_id' => $studentId,
            'lecture_id' => $request->lecture_id,
            'type' => 'logbook-daily',
            'date' => $request->date,
            'status' => 0,
            'field_1' => $request->no_catatan_medik,
            'field_2' => $request->rawat_inap,
            'field_3' => $request->rawat_jalan,
        ]);

        foreach (array_unique($request->competence_ids ?? []) as $formOptionId) {
            StudentLogSkill::create([
                'student_id' => $studentId,
                'student_log_id' => $logbook->id,
                'form_option_id' => $formOptionId,
            ]);
        }

        return response()->json([
            'success' => true,
            'text' => 'Create Logbook Success',
            'result' => $logbook,
        ]);
    }

    public function updateDaily(Request $request, $id)
    {
        $logbook = StudentLog::find($id);
        if (!$logbook) {
            return response()->json([
                'success' => false,
                'text' => 'Logbook not found',
                'result' => null,
            ], 404);
        }

        $this->validate($request, [
            'date' => 'required|date',
            'no_catatan_medik' => 'required|string',
            'rawat_inap' => 'nullable|string',
            'rawat_jalan' => 'nullable|string',
            'lecture_id' => 'nullable|integer',
            'competence_ids' => 'nullable|array',
            'competence_ids.*' => 'integer',
        ]);

        $logbook->update([
            'lecture_id' => $request->lecture_id,
            'date' => $request->date,
            'field_1' => $request->no_catatan_medik,
            'field_2' => $request->rawat_inap,
            'field_3' => $request->rawat_jalan,
        ]);

        StudentLogSkill::where('student_log_id', $id)->delete();
        foreach (array_unique($request->competence_ids ?? []) as $formOptionId) {
            StudentLogSkill::create([
                'student_id' => $logbook->student_id,
                'student_log_id' => $logbook->id,
                'form_option_id' => $formOptionId,
            ]);
        }

        return response()->json([
            'success' => true,
            'text' => 'Update Logbook Success',
            'result' => $logbook,
        ]);
    }

    public function competenceOptions(Request $request)
    {
        $studentId = $this->resolveStudentId($request);

        $options = FormOption::whereStatus(1)
            ->whereType('sp1ipd-logbook-competence')
            ->orderBy('name')
            ->get();

        $counts = StudentLogSkill::whereStudentId($studentId)
            ->select(DB::raw('count(*) as count, form_option_id'))
            ->groupBy('form_option_id')
            ->whereIn('form_option_id', $options->pluck('id')->toArray())
            ->get();

        foreach ($options as $option) {
            $matched = $counts->where('form_option_id', $option->id)->first();
            $option->setAttribute('count', $matched ? (int) $matched['count'] : 0);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Competence Options Success',
            'result' => $options,
        ]);
    }

    public function store(Request $request)
    {
        $studentId = $this->resolveStudentId($request);

        $request->merge([
            'student_id' => $studentId,
            'status' => 0,
            'date' => $request->date ?? date('Y-m-d'),
        ]);

        $this->validateData($request);

        $logbook = StudentLog::create($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Create Logbook Success',
            'result' => $logbook,
        ]);
    }

    public function update(Request $request, $id)
    {
        $studentId = $this->resolveStudentId($request);

        $request->merge([
            'student_id' => $studentId,
            'status' => 0,
            'date' => $request->date ?? date('Y-m-d'),
        ]);

        $this->validateData($request);

        $logbook = StudentLog::find($id);
        if (!$logbook) {
            return response()->json([
                'success' => false,
                'text' => 'Logbook not found',
                'result' => null,
            ], 404);
        }

        $logbook->update([
            'student_id' => $request->student_id,
            'stase_id' => $request->stase_id,
            'type' => $request->type,
            'category' => $request->category,
            'lecture_id' => $request->lecture_id,
            'date' => $request->date,
            'status' => 0,
            'field_1' => $request->field_1,
            'field_2' => $request->field_2,
            'field_3' => $request->field_3,
            'field_4' => $request->field_4,
            'field_5' => $request->field_5,
            'field_6' => $request->field_6,
        ]);

        if ($request->skills) {
            $skills = [];
            foreach ($request->skills as $key => $value) {
                if ($value) {
                    $skills[] = $key;
                }
            }

            StudentLogSkill::whereStudentLogId($id)->delete();
            foreach ($skills as $skill) {
                StudentLogSkill::create([
                    'student_id' => $logbook->student_id,
                    'stase_id' => $logbook->stase_id,
                    'student_log_id' => $logbook->id,
                    'form_option_id' => $skill,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'text' => 'Update Logbook Success',
            'result' => $logbook,
        ]);
    }

    public function show($id)
    {
        $logbook = StudentLog::with(['lecture', 'stase', 'stase_log_skills'])->find($id);

        if (!$logbook) {
            return response()->json([
                'success' => false,
                'text' => 'Logbook not found',
                'result' => null,
            ], 404);
        }

        $staseId = request()->query('stase_id') ?? $logbook->stase_id;
        $types = collect();
        $skills = collect();
        $skillCount = collect();

        if ($staseId) {
            $options = FormOption::whereStatus(1)
                ->whereRelationId($staseId)
                ->get();
            $types = $options->where('type', 'stase-logbook')->flatten();
            $skills = $options->where('type', 'logbook-skill')->flatten();

            $studentId = $this->resolveStudentId(request());
            $skillCount = StudentLogSkill::whereStudentId($studentId)
                ->select(DB::raw('count(*) as count, form_option_id'))
                ->groupBy('form_option_id')
                ->whereIn('form_option_id', $skills->pluck('id')->toArray())
                ->get();

            foreach ($skills as $skill) {
                $selected = $skillCount->where('form_option_id', $skill->id)->first();
                $skill->setAttribute('count', $selected ? $selected['count'] : 0);
            }
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Logbook Success',
            'result' => [
                'logbook' => $logbook,
                'options' => [
                    'types' => $types,
                    'skills' => $skills,
                    'skill_count' => $skillCount,
                ],
            ],
        ]);
    }

    public function destroy($id)
    {
        $logbook = StudentLog::find($id);
        if (!$logbook) {
            return response()->json([
                'success' => false,
                'text' => 'Logbook not found',
                'result' => null,
            ], 404);
        }

        $logbook->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Logbook Success',
            'result' => null,
        ]);
    }

    public function validateData($request)
    {
        $this->validate($request, [
            'student_id' => 'required|integer',
            'stase_id' => 'required|integer',
            'type' => 'required',
            'category' => 'nullable',
            'lecture_id' => 'nullable|integer',
            'date' => 'nullable|date',
        ]);
    }

    public function withFilter($dataContent, $request)
    {
        if ($request->stase_id != null) {
            $dataContent = $dataContent->where('student_logs.stase_id', $request->stase_id);
        }

        if ($request->status != null) {
            $dataContent = $dataContent->where('student_logs.status', $request->status);
        }

        if ($request->lecture_id != null) {
            $dataContent = $dataContent->where('student_logs.lecture_id', $request->lecture_id);
        }

        if ($request->student_id != null) {
            $dataContent = $dataContent->where('student_logs.student_id', $request->student_id);
        }

        if ($request->type != null) {
            $dataContent = $dataContent->where('student_logs.type', $request->type);
        }

        if ($request->date != null) {
            $dataContent = $dataContent->whereDate('student_logs.date', $request->date);
        }

        if ($request->keyword != null) {
            $dataContent = $dataContent->where(function ($q) use ($request) {
                $q->where('student_logs.field_1', 'LIKE', '%' . $request->keyword . '%');
                $q->orWhere('student_logs.field_2', 'LIKE', '%' . $request->keyword . '%');
                $q->orWhere('student_logs.field_3', 'LIKE', '%' . $request->keyword . '%');
            });
        }

        return $dataContent;
    }

    private function withApproveFilter($dataContent, Request $request)
    {
        if ($request->stase_id != null) {
            $dataContent = $dataContent->where('stase_id', $request->stase_id);
        }

        if ($request->status != null) {
            $dataContent = $dataContent->where('status', $request->status);
        }

        if ($request->lecture_id != null) {
            $dataContent = $dataContent->where('lecture_id', $request->lecture_id);
        }

        if ($request->student_id != null) {
            $dataContent = $dataContent->where('student_id', $request->student_id);
        }

        if ($request->type != null) {
            $dataContent = $dataContent->where('type', $request->type);
        }

        if ($request->keyword != null) {
            $dataContent = $dataContent->where(function ($q) use ($request) {
                $q->where('field_1', 'LIKE', '%' . $request->keyword . '%');
                $q->orWhere('field_2', 'LIKE', '%' . $request->keyword . '%');
                $q->orWhere('field_3', 'LIKE', '%' . $request->keyword . '%');
            });
        }

        return $dataContent;
    }

    public function staseOption(Request $request, $stase_id)
    {
        $options = FormOption::whereStatus(1)
            ->whereRelationId($stase_id)
            ->get();
        $types = $options->where('type', 'stase-logbook')->flatten();
        $skills = $options->where('type', 'logbook-skill')->flatten();

        $studentId = $this->resolveStudentId($request);

        $skillCount = StudentLogSkill::whereStudentId($studentId)
            ->select(DB::raw('count(*) as count, form_option_id'))
            ->groupBy('form_option_id')
            ->whereIn('form_option_id', $skills->pluck('id')->toArray())
            ->get();

        foreach ($skills as $skill) {
            $selected = $skillCount->where('form_option_id', $skill->id)->first();
            $skill->setAttribute('count', $selected ? $selected['count'] : 0);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Stase Options Success',
            'result' => [
                'types' => $types,
                'skills' => $skills,
                'skill_count' => $skillCount,
            ],
        ]);
    }

    public function studentLog(Request $request, $stase_id)
    {
        $studentId = $this->resolveStudentId($request);

        if (!$studentId) {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 401);
        }

        $data = $this->buildStudentLogData($studentId, $stase_id);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Student Logbook Success',
            'result' => $data['result'],
            'categories' => $data['categories'],
        ]);
    }

    public function print($student_id, $stase_id)
    {
        $student = Student::find($student_id);
        $stase = Stase::find($stase_id);

        if (!$student || !$stase) {
            return response()->json([
                'success' => false,
                'text' => 'Student or stase not found',
                'result' => null,
            ], 404);
        }

        $data = $this->buildStudentLogData($student->id, $stase->id, false);
        $name = 'Logbook - ' . $student->name . ' - ' . $stase->name;

        return view('templates.pdf.logbook', [
            'data' => $data['result'],
            'student' => $student,
            'stase' => $stase,
            'name' => $name,
        ]);
    }

    public function printStudentLogbook(Request $request)
    {
        $student = Student::whereLinkToken($request->link_token)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'text' => 'Student not found',
                'result' => null,
            ], 404);
        }

        return view('templates.pdf.student_logbook', $this->buildStudentLogbookViewData($student));
    }

    /**
     * Export logbook PDFs for every student whose `year` falls within the
     * given range, saving each rendered document to storage.
     */
    public function exportStudentLogbook(Request $request)
    {
        $request->validate([
            'from_year' => 'required',
            'to_year' => 'required',
        ]);

        $result = $this->generateStudentLogbooks(
            $request->from_year,
            $request->to_year
        );

        return response()->json([
            'success' => true,
            'text' => $result['count'] . ' logbook(s) exported',
            'result' => $result,
        ]);
    }

    /**
     * Render and save a logbook PDF for every student whose `year` falls
     * within the given (inclusive) range. Returns a summary of what was
     * written. Shared by the HTTP route and the console command.
     *
     * @param  callable|null  $onProgress  called as ($student, $path) after each save.
     */
    public function generateStudentLogbooks($fromYear, $toYear, ?callable $onProgress = null)
    {
        // `year` is stored as 'YYYY-MM' (e.g. '2025-07'), so match on the
        // 4-char year prefix rather than the full value.
        $students = Student::whereBetween(DB::raw('LEFT(year, 4)'), [$fromYear, $toYear])
            ->orderBy('year')
            ->orderBy('name')
            ->get();

        $directory = 'logbooks/' . $fromYear . '-' . $toYear;
        $exported = [];

        foreach ($students as $student) {
            $pdf = PDF::loadView(
                'templates.pdf.student_logbook',
                $this->buildStudentLogbookViewData($student)
            )->setPaper('a4');

            $fileName = $student->year . '-' . Str::slug($student->name) . '-' . $student->id . '.pdf';
            $path = $directory . '/' . $fileName;

            Storage::put($path, $pdf->output());

            $exported[] = $path;

            if ($onProgress) {
                $onProgress($student, $path);
            }
        }

        return [
            'from_year' => $fromYear,
            'to_year' => $toYear,
            'count' => count($exported),
            'directory' => $directory,
            'files' => $exported,
        ];
    }

    /**
     * Build the view data for a single student's logbook document.
     * Shared by printStudentLogbook (HTML) and exportStudentLogbook (PDF).
     */
    private function buildStudentLogbookViewData(Student $student)
    {
        $student_id = $student->id;

        $student_profile = StudentProfile::whereStudentId($student_id)->first();

        $stases = Stase::orderBy('desc')->orderBy('stase_order')->get();

        $stase_logs = StaseLog::whereStudentId($student_id)
            ->with('stase')
            ->orderBy('stase_id')
            ->get();

        $student_logs = StudentLog::with(['lecture'])
            ->whereStudentId($student_id)
            ->orderBy('date')
            ->get();

        $form_options = FormOption::whereIn('type', ['stase-logbook', 'logbook-skill'])->get();

        $skill_counts = StudentLogSkill::whereStudentId($student_id)
            ->select('form_option_id', DB::raw('count(*) as total'))
            ->groupBy('form_option_id')
            ->pluck('total', 'form_option_id');

        $logbook_skills = $form_options->where('type', 'logbook-skill')->values();
        foreach ($logbook_skills as $skill) {
            $skill->setAttribute('count', $skill_counts[$skill->id] ?? 0);
            $stase = $stases->firstWhere('id', $skill->relation_id);
            $skill->setAttribute('stase_name', $stase ? $stase->name : '');
        }

        foreach ($stases as $stase) {
            $logbook_sections = $form_options
                ->where('type', 'stase-logbook')
                ->where('relation_id', $stase->id)
                ->values();

            $hasData = false;
            foreach ($logbook_sections as $section) {
                $data = $student_logs
                    ->where('type', $section->value)
                    ->where('stase_id', $stase->id)
                    ->values();
                $section->setAttribute('data', $data);

                if ($data->isNotEmpty()) {
                    $hasData = true;
                }
            }

            $stase->setAttribute('logbook_sections', $logbook_sections);
            $stase->setAttribute('has_data', $hasData);
        }

        // Mandatory stases always show; non-mandatory only when they have logbook data.
        $stases = $stases
            ->filter(fn ($stase) => $stase->is_mandatory || $stase->has_data)
            ->values();

        return [
            'student' => $student,
            'student_profile' => $student_profile,
            'stases' => $stases,
            'stase_logs' => $stase_logs,
            'student_logs' => $student_logs,
            'form_options' => $form_options,
            'logbook_skills' => $logbook_skills,
            // Base64 data URIs so images render under DomPDF (which cannot
            // resolve root-relative URLs or draw inline <svg>).
            'logo' => $this->logoDataUri(),
            'qr' => $this->qrDataUri(
                url('/print/student-logbook?link_token=' . $student->link_token)
            ),
        ];
    }

    /**
     * UGM logo as a base64 PNG data URI (empty string if the file is missing).
     */
    private function logoDataUri()
    {
        $path = public_path('assets/images/logo-ugm.png');

        if (!is_file($path)) {
            return '';
        }

        return 'data:image/png;base64,' . base64_encode(file_get_contents($path));
    }

    /**
     * QR code for the given URL as a base64 SVG data URI. DomPDF renders SVG
     * supplied via <img> (through php-svg-lib), unlike inline <svg>.
     */
    private function qrDataUri($url)
    {
        $svg = QrCode::format('svg')->size(80)->margin(0)->generate($url);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    private function resolveStudentId(Request $request)
    {
        $payload = $request->attributes->get('jwt_payload');
        $logAsType = $payload ? data_get($payload, 'log_as_auth_type') : null;
        $logAsId = $payload ? data_get($payload, 'log_as_auth_id') : null;
        $authType = $payload ? data_get($payload, 'auth_type') : null;
        $authId = $payload ? data_get($payload, 'auth_id') : null;

        if ($logAsType === 'student' && $logAsId) {
            return $logAsId;
        }

        if ($authType === 'student' && $authId) {
            return $authId;
        }

        return $request->student_id;
    }

    private function resolveLectureId(Request $request)
    {
        $payload = $request->attributes->get('jwt_payload');
        $logAsType = $payload ? data_get($payload, 'log_as_auth_type') : null;
        $logAsId = $payload ? data_get($payload, 'log_as_auth_id') : null;
        $authType = $payload ? data_get($payload, 'auth_type') : null;
        $authId = $payload ? data_get($payload, 'auth_id') : null;

        if ($logAsType === 'lecture' && $logAsId) {
            return $logAsId;
        }

        if ($authType === 'lecture' && $authId) {
            return $authId;
        }

        return null;
    }

    private function buildStudentLogData($studentId, $staseId, $orderDesc = true)
    {
        $group = StudentLog::with(['lecture'])
            ->whereStudentId($studentId)
            ->whereStaseId($staseId)
            ->when($orderDesc, function ($query) {
                $query->orderByDesc('date');
            }, function ($query) {
                $query->orderBy('date');
            })
            ->get();

        $formData = FormOption::whereRelationId($staseId)
            ->whereType('stase-logbook')
            ->get();

        $categories = FormOption::whereRelationId($staseId)
            ->whereType('logbook-cat')
            ->get();

        $studentLogSkills = StudentLogSkill::whereStudentId($studentId)
            ->whereStaseId($staseId)
            ->get();

        foreach ($formData as $data) {
            $data->setAttribute('logbook', $group->where('type', $data->value)->flatten());
        }

        $result = [];
        foreach ($formData as $data) {
            if (count($data['logbook']) > 0 || $data['status'] == 1) {
                $result[] = $data;
            }
        }

        foreach ($result as $item) {
            foreach ($item['logbook'] as $logbook) {
                $skillList = $studentLogSkills->where('student_log_id', $logbook['id'])->flatten();
                $skills = [];
                foreach ($skillList as $skill) {
                    $skills[$skill->form_option_id] = true;
                }
                $logbook->setAttribute('skills', $skills);
            }
        }

        return [
            'result' => $result,
            'categories' => $categories,
        ];
    }
}
