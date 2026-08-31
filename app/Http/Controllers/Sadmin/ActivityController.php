<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Lecture;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = Activity::with([
            'creator',
//            'pengampu',
//            'pembimbing',
//            'penguji'
        ])
            ->where('status', '!=', 'draft')
            ->orderByDesc('start_date');

        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);
        return $dataContent;
    }

    public function store(Request $request)
    {
        $this->validateData($request);
        $request->merge([
            'created_by' => 0,
        ]);

        Activity::create($request->all());
    }

    public function show($id) {
        $data = Activity::with([
            'creator',
            'activity_lectures' => function ($q) {
                $q->with('lecture');
            },
            'activity_students' => function ($q) {
                $q->with('student');
            },
        ])->find($id);

        $data['penguji'] = Lecture::whereIn('id', json_decode($data['lecture_penguji'], true))->get();
        $data['pembimbing'] = Lecture::whereIn('id', json_decode($data['lecture_pembimbing'], true))->get();
        $data['pengampu'] = Lecture::whereIn('id', json_decode($data['lecture_pengampu'], true))->get();

        return $data;
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);
        $request->merge([
            'status'     => 'active',
        ]);

        Activity::find($id)->update($request->all());

        return $this->response;
    }

    public function destroy($id)
    {
        $dataContent = Activity::findOrFail($id)->delete();
    }

    public function validateData($request){
        $this->validate($request, [
            "name"          => 'required',
//            "start_date"    => 'required',
        ]);
    }

    public function withFilter($dataContent, $request){
        if ($request->keyword != null){
            $dataContent = $dataContent->where(function ($q)use ($request){
                $q->where('name', 'LIKE', '%'.$request->keyword.'%')
                    ->orWhere('speaker', 'LIKE', '%'.$request->keyword.'%');
            });
        }

        if ($request->lecture_id != null){
            $dataContent = $dataContent->where(function ($q)use ($request){
                $q->where('lecture_pembimbing', 'LIKE', '%'.$request->lecture_id.','.'%')
                    ->orWhere('lecture_pembimbing', 'LIKE', '%'.$request->lecture_id.']'.'%')
                    ->orWhere('lecture_penguji', 'LIKE', '%'.$request->lecture_id.','.'%')
                    ->orWhere('lecture_penguji', 'LIKE', '%'.$request->lecture_id.']'.'%')
                    ->orWhere('lecture_pengampu', 'LIKE', '%'.$request->lecture_id.','.'%')
                    ->orWhere('lecture_pengampu', 'LIKE', '%'.$request->lecture_id.']'.'%');
            });
        }

        if ($request->start_date != null){
            $dataContent = $dataContent->withStartDate($request->start_date);
        }

        if ($request->end_date != null){
            $dataContent = $dataContent->withEndDate($request->end_date);
        }
        return $dataContent;
    }

    /**
     * Printable report of every activity whose start_date falls within
     * [start_date, end_date]. Rendered as a Blade view opened in a new tab
     * from the SPA (auth via the jwt.query middleware / ?token=).
     *
     * All lectures and students are fetched once up front and then mapped
     * onto each activity, so the per-activity lecturer id lists (stored as
     * JSON on activities.lecture_pembimbing/penguji/pengampu) and the
     * activity_students presence rows resolve without extra queries.
     */
    public function printReport(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $lectures = Lecture::get()->keyBy('id');
        $students = Student::get()->keyBy('id');

        $activities = Activity::with(['activity_students' => function ($q) {
                $q->orderBy('created_at');
            }])
            ->where('status', '!=', 'draft')
            ->when($startDate, fn ($q) => $q->whereDate('start_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('start_date', '<=', $endDate))
            ->orderBy('start_date')
            ->get();

        $lecturesFor = function ($raw) use ($lectures) {
            $ids = is_array($raw) ? $raw : json_decode($raw ?: '[]', true);
            if (!is_array($ids)) {
                $ids = [];
            }

            return collect($ids)
                ->map(fn ($id) => $lectures->get((int) $id))
                ->filter()
                ->values();
        };

        $activities->each(function ($activity) use ($lecturesFor, $students) {
            $activity->setAttribute('pembimbing_list', $lecturesFor($activity->lecture_pembimbing));
            $activity->setAttribute('penguji_list', $lecturesFor($activity->lecture_penguji));
            $activity->setAttribute('pengampu_list', $lecturesFor($activity->lecture_pengampu));

            $activity->activity_students->each(function ($row) use ($students) {
                $row->setRelation('student', $students->get((int) $row->student_id));
            });
        });

        return view('templates.pdf.activity_report', [
            'activities' => $activities,
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ]);
    }
}
