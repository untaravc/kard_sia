<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Imports\StaseLogImport;
use App\Models\Document;
use App\Models\FormOption;
use App\Models\Lecture;
use App\Models\Object\Category;
use App\Models\OpenStaseTask;
use App\Models\Stase;
use App\Models\StaseTask;
use App\Models\StaseTaskLog;
use App\Models\Student;
use App\Models\StudentLogSkill;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class GlobalFunctionController extends Controller
{
    public function getStase()
    {
        return Stase::orderBy('name')->get();
    }

    public function getStaseTask($id)
    {
        return StaseTask::whereStaseId($id)
            ->orderBy('name')
            ->get();
    }

    public function getStudentStase($id)
    {
        $takenStase = Student::find($id)->staseLogs()->pluck('stase_id');
        return Stase::whereNotIn('id', $takenStase)->orderBy('name')->get();
    }

    public function getLectures()
    {
        return Lecture::orderBy('name')->whereStatus('active')->get();
    }

    public function getTasks()
    {
        return Task::orderBy('name')->get();
    }

    public function documentCategories(Request $request)
    {
        $cat = Document::whereNotNull('lecture_id')
            ->groupBy('category')
            ->get(['category', 'id']);

        foreach ($cat as $item) {
            $num = Document::whereCategory($item->category);

            if ($request->lecture_id != null) {
                $num = $num->whereLectureId($request->lecture_id);
            }

            $item['num'] = $num->count();
        }
        return $cat;
    }

    public function uploadExcel(Request $request)
    {
        $rows = Excel::toArray(new StaseLogImport(), $request->file('file'))[0];

        $array = [];
        for ($i = 0; $i < count($rows); $i++) {
            $box = explode('-', $rows[$i][1]);
            $start = explode(' ', $box[0]);
            $end = explode(' ', $box[1]);

            $start_date = $start[0] . '-' . $this->getMoNum($start[1]) . '-' . $start[2];
            $end_date = $end[0] . '-' . $this->getMoNum($end[1]) . '-' . $end[2];

            $array[$i]['start_date'] = $start_date;
            $array[$i]['end_date'] = $end_date;
            $array[$i]['stase_id'] = $rows[$i][3];
        }

        return $array;
    }

    public function getMoNum($str)
    {
        $start_mo = 0;
        switch ($str) {
            case 'Januari':
                $start_mo = 1;
                break;
            case 'Februari' :
                $start_mo = 2;
                break;
            case 'Maret' :
                $start_mo = 3;
                break;
            case 'April' :
                $start_mo = 4;
                break;
            case 'Mei' :
                $start_mo = 5;
                break;
            case 'Juni' :
                $start_mo = 6;
                break;
            case 'Juli' :
                $start_mo = 7;
                break;
            case 'Agustus' :
                $start_mo = 8;
                break;
            case 'September' :
                $start_mo = 9;
                break;
            case 'Oktober' :
                $start_mo = 10;
                break;
            case 'November' :
                $start_mo = 11;
                break;
            case 'Desember' :
                $start_mo = 12;
                break;
        }

        return $start_mo;
    }

    public function lecture($id)
    {
        return Lecture::with([
            'lectureProfile'
        ])->find($id);
    }

    public function openLink(Request $request, $slug)
    {
        switch ($slug) {
            case 'ost':
                return $this->link_open_stase_task($request);
            default:
                return 'ups';
        }
    }

    public function link_open_stase_task($request)
    {
        // Cek dosen
        $lecture = Lecture::whereLinkToken($request->llt)->first();
        if (!$lecture) {
            return 'oops..';
        }
        // Cek mhs
        $student = Student::whereLinkToken($request->slt)->first();
        if (!$lecture) {
            return 'oops..';
        }
        // Cek open
        $open = OpenStaseTask::whereStudentId($lecture->id)
            ->whereLectureId($student)
            ->whereLinkToken($request->olt)
            ->first();
        if (!$open) {
            return 'oops..';
        }
        //check empty
        $empty = StaseTaskLog::whereStudentId($lecture->id)
            ->whereLectureId($student)
            ->wherePointAverage(null)
            ->first();
        if (!$empty) {
            return 'oops..';
        }
        // Login Dosen
        Auth::guard('lecture')->login($lecture);
        //Redirect Link
        return redirect('/dosen/task/scoring/' . $open->id);
    }

    public function getActCats()
    {
        return [
            [
                'id' => 1,
                'name' => 'Stase (CBD, Jurnal book reading, dll)',
            ],
            [
                'id' => 2,
                'name' => 'Tesis (Outline, Semhas, Sempro, Tesis)',
            ],
            [
                'id' => 3,
                'name' => 'Laporan Jaga',
            ],
            [
                'id' => 4,
                'name' => 'Ilmiah Divisi',
            ],
            [
                'id' => 5,
                'name' => 'Club',
            ],
            [
                'id' => 6,
                'name' => 'Konferensi',
            ],
            [
                'id' => 7,
                'name' => 'Seminar Kasus / Lapsus',
            ],
            [
                'id' => 8,
                'name' => 'Referat',
            ],
            [
                'id' => 0,
                'name' => 'Lain-lain',
            ],
        ];
    }

    public function email_confirmation(Request $request)
    {
        if (!$request->email) {
            return 'invalid';
        }

        $student = Student::whereEmail($request->email)->first();

        if (!$student) {
            return 'invalid';
        }

        $student->update([
            'validated_at' => now()
        ]);

        return redirect('/resident');
    }

    public function staseOption($id)
    {
        $options = FormOption::whereStatus(1)
            ->whereRelationId($id)
            ->get();
        $option = $options->where('type', 'stase-logbook')->flatten();
        $skills = $options->where('type', 'logbook-skill')->flatten();

        $auth_id = Auth::guard('student')->user()->id;

        $skill_count = StudentLogSkill::whereStudentId($auth_id)
            ->select(DB::raw('count(*) as count, form_option_id'))
            ->groupBy('form_option_id')
            ->whereIn('form_option_id', $skills->pluck('id')->toArray())
            ->get();

        foreach ($skills as $skill) {
            $selected = $skill_count->where('form_option_id', $skill->id)->first();
            if ($selected) {
                $skill->setAttribute('count', $selected['count']);
            } else {
                $skill->setAttribute('count', 0);
            }
        }

        return [
            "types" => $option,
            "skills" => $skills,
            "skill_count" => $skill_count,
        ];
    }

    public function getCategories()
    {
        $categories = Category::orderBy('name')->get();
        $this->response['result'] = $categories;
        return $this->response;
    }

    public function staseList()
    {
        $this->response['result'] = Stase::orderBy('name')->get();
        return $this->response;
    }
}
