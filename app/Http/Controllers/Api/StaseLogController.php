<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StaseLog;
use App\Models\Student;
use Illuminate\Http\Request;

class StaseLogController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = StaseLog::leftJoin('students', 'students.id', '=', 'stase_logs.student_id')
            ->leftJoin('stases', 'stases.id', '=', 'stase_logs.stase_id')
            ->select(
                'stase_logs.*',
                'students.name as student_name',
                'students.email as student_email',
                'students.status as student_status',
                'stases.name as stase_name',
                'stases.alias as stase_alias'
            )
            ->orderByDesc('stase_logs.start_date');

        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Stase Logs Success',
            'result' => $dataContent,
        ]);
    }

    public function staseLogCheck()
    {
        $today = now()->toDateString();

        $students = Student::where('status', 'active')
            ->whereDoesntHave('staseLogs', function ($query) use ($today) {
                $query->whereDate('start_date', '<=', $today)
                    ->whereDate('end_date', '>=', $today);
            })
            ->select('id', 'name', 'email', 'year', 'status')
            ->orderBy('year')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Students Without Active Stase Logs Success',
            'result' => $students,
        ]);
    }

    protected function withFilter($dataContent, Request $request)
    {
        $staseIds = $this->normalizeArrayFilter($request->stase_ids);
        if (!empty($staseIds)) {
            $dataContent = $dataContent->whereIn('stase_logs.stase_id', $staseIds);
        }

        $studentIds = $this->normalizeArrayFilter($request->student_ids);
        if (!empty($studentIds)) {
            $dataContent = $dataContent->whereIn('stase_logs.student_id', $studentIds);
        }

        if ($request->date != null) {
            $dataContent = $dataContent
                ->whereDate('stase_logs.start_date', '<=', $request->date)
                ->whereDate('stase_logs.end_date', '>=', $request->date);
        }

        return $dataContent;
    }

    protected function normalizeArrayFilter($value)
    {
        if (is_array($value)) {
            return array_values(array_filter($value, function ($item) {
                return $item !== null && $item !== '';
            }));
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return array_values(array_filter($decoded, function ($item) {
                    return $item !== null && $item !== '';
                }));
            }

            return array_values(array_filter(explode(',', $value), function ($item) {
                return $item !== null && trim($item) !== '';
            }));
        }

        return [];
    }
}
