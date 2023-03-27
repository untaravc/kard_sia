<?php

namespace App\Http\Controllers\Sadmin;

use App\Exports\DefaultExport;
use App\Exports\PenilaianExport;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\FormOption;
use App\Models\Lecture;
use App\Models\Stase;
use App\Models\StaseLog;
use App\Models\StaseTask;
use App\Models\StaseTaskLog;
use App\Models\StudentLog;
use App\Models\Task;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\TemplateProcessor;

class DownloadController extends Controller
{
    private $kps = [
        'name'    => 'dr. Anggoro Budi Hartopo, MSc, SpPD, PhD, SpJP',
        'nip'     => '197807182010121004',
        'gol'     => 'Penata Tingkat I / III D',
        'jabatan' => 'Ketua Program Studi Jantung dan Pembuluh Darah'
    ];

    private function logo_ugm(){
        $file_logo = public_path('assets/images/logo-ugm.png');
        return base64_encode(file_get_contents($file_logo));
    }

    private function ttd(){
        $file_ttd = public_path('assets/ttd/ttd-abh.jpg');
        return base64_encode(file_get_contents($file_ttd));
    }

    public function penilaian(Request $request)
    {
        $query = [];
        $query['start'] = $request->start_date != null ? $request->start_date . ' 00:00:00' : date('Y-m') . '-01 00:00:00';
        $query['end'] = $request->end_date != null ? $request->end_date . ' 23:59:59' : date('Y-m-d') . ' 23:59;59';

        $stase_task_logs = StaseTaskLog::whereBetween('date', [$query['start'], $query['end']])
            ->with([
                'student',
                'lecture',
                'staseTask',
                'stase',
            ])
            ->orderBy('student_id')
            ->get();

//        return $stase_task_logs;
//        return view('templates.excel.penilaian', [
//            'dataContent'   => $stase_task_logs
//        ]);
        $data = [
            'dataContent' => $stase_task_logs,
            'template'    => 'templates.excel.penilaian',
            'query'       => $query,
        ];

        return Excel::download(new DefaultExport($data), 'Penilaian.xls');

    }

    public function activities(Request $request)
    {
        $query = [];
        $query['start'] = $request->start_date != null ? $request->start_date . ' 00:00:00' : date('Y-m') . '-01 00:00:00';
        $query['end'] = $request->end_date != null ? $request->end_date . ' 23:59:59' : date('Y-m-d') . ' 23:59;59';
//        return $query;
        $activities = Activity::whereBetween('start_date', [$query['start'], $query['end']])
            ->orderBy('start_date')
            ->get();

//        return $activities;
//        return view('templates.excel.activities', [
//            'dataContent'   => $activities
//        ]);
        $data = [
            'dataContent' => $activities,
            'template'    => 'templates.excel.activities',
            'query'       => $query,
        ];

        return Excel::download(new DefaultExport($data), 'Agenda Prodi.xls');
    }

    public function letter_generator(Request $request)
    {
        $template = new TemplateProcessor(public_path('/assets/docs/template-1.docx'));
        $template->setValue('title', 'Makanan enak banget.');
        $name = time();
        $template->saveAs($name . '.docx');
        return response()->download($name . '.docx')->deleteFileAfterSend(true);
    }

    public function sk_referat_lapsus($activity_id)
    {
        $activity = Activity::whereIn('category', [7, 8])
            ->where('id', $activity_id)
            ->first();

        if ($activity == null) {
            return 'Kateori aktifitas bukan referat/lapsus.';
        }

        $file_logo = public_path('assets/images/logo-ugm.png');
        $logo = base64_encode(file_get_contents($file_logo));

        $file_ttd = public_path('assets/ttd/ttd-abh.jpg');
        $ttd = base64_encode(file_get_contents($file_ttd));

        $date = substr($activity->start_date, 5, 2);
        $date .= substr($activity->start_date, 8, 2);
        $data['number'] = $date . "/UN1/FKKMK.2/JP.1/AK/" . date('Y');
        $data['type'] = $activity->category === 7 ? 'Seminar Kasus' : 'Referat';

        $data['activity'] = $activity;
        $data['date'] = substr($activity->start_date, 8, 2) . ' ';
        $data['date'] .= $this->bulan((int)substr($activity->start_date, 5, 2)) . ' ';
        $data['date'] .= substr($activity->start_date, 0, 4);
        $data['day'] = $this->hari(date('w', strtotime($activity->start_date)));

//        return view('templates.pdf.sk_referat', compact(
//            'logo',
//            'data',
//            'ttd'
//        ));

        $pdf = PDF::loadView('templates.pdf.sk_referat', compact(
            'data',
            'logo',
            'ttd'
        ))
            ->setPaper('a4');

        return $pdf->download('SK ' . $activity->name . ' ' . $data['number'] . '.pdf');
    }

    public function sk_stase(Request $request)
    {
        $data['logo'] = $this->logo_ugm();
        $data['ttd'] = $this->ttd();
        $data['date'] = date('Y-m-d');
        $data['kps'] = $this->kps;

        $data['stase_log'] = StaseLog::with('stase', 'student', 'student_profile')->find($request->stase_log);
        $data['number'] = letter_stase_number('SKSTS', $data['stase_log']);
        $data['stase'] = Stase::find($data['stase_log']['stase_id']);
        $data['lectures'] = explode(';', $data['stase']['lecture_names']);

        $data['year'] = substr($data['stase_log']['start_date'], 0, 4);
        $data['sem'] = substr($data['stase_log']['start_date'], 5, 2) > 6 ? 'Gasal' : 'Genap';
        $data['start_str'] = date_indo_str($data['stase_log']['start_date']);
        $data['end_str'] = date_indo_str($data['stase_log']['end_date']);

        return view('templates.pdf.sk_stase', $data);
    }

    public function permohonan_stase(Request $request)
    {
        $data['logo'] = $this->logo_ugm();
        $data['ttd'] = $this->ttd();
        $data['date'] = date('Y-m-d');
        $data['kps'] = $this->kps;

        $data['stase_log'] = StaseLog::with('stase', 'student', 'student_profile')->find($request->stase_log);
        $data['number'] = letter_stase_number('SPSTS', $data['stase_log']);
        $task_active_ids = Task::whereIsLatter(1)->pluck('id');
        $data['stase_tasks'] = StaseTask::whereStaseId($data['stase_log']['stase_id'])
            ->whereIn('task_id', $task_active_ids)->get();
        $data['stase'] = Stase::find($data['stase_log']['stase_id']);

        $time = strtotime($data['stase_log']['end_date']) - strtotime($data['stase_log']['start_date']);
        $data['count_week'] = ceil($time / 60 / 60 / 24 / 7);
        $data['start_str'] = date_indo_str($data['stase_log']['start_date']);
        $data['end_str'] = date_indo_str($data['stase_log']['end_date']);
        $data['date_str'] = date_indo_str(date('Y-m-d',strtotime($data['stase_log']['start_date'] . '-7days')));

        return view('templates.pdf.permohonan_stase', $data);
    }

    public function pembimbing_stase(Request $request)
    {
        $data['logo'] = $this->logo_ugm();
        $data['ttd'] = $this->ttd();
        $data['date'] = date('Y-m-d');
        $data['kps'] = $this->kps;

        $data['stase_log'] = StaseLog::with('stase', 'student', 'student_profile')->find($request->stase_log);
        $data['number'] = letter_stase_number('SBSTS', $data['stase_log']);
        $data['stase'] = Stase::find($data['stase_log']['stase_id']);
        $data['lectures'] = explode(';', $data['stase']['lecture_names']);

        $data['year'] = substr($data['stase_log']['start_date'], 0, 4);
        $data['sem'] = substr($data['stase_log']['start_date'], 5, 2) > 6 ? 'Gasal' : 'Genap';
        $data['start_str'] = date_indo_str($data['stase_log']['start_date']);
        $data['end_str'] = date_indo_str($data['stase_log']['end_date']);

        return view('templates.pdf.pembimbing_stase', $data);
    }

    public function undangan_referat_lapsus($activity_id){
        $activity = Activity::whereIn('category', [7, 8])
            ->with('stase')
            ->where('id', $activity_id)
            ->first();

        if ($activity == null) {
            return 'Kateori aktifitas bukan referat/lapsus.';
        }

        $file_logo = public_path('assets/images/logo-ugm.png');
        $logo = base64_encode(file_get_contents($file_logo));

        $file_ttd = public_path('assets/ttd/ttd-abh.jpg');
        $ttd = base64_encode(file_get_contents($file_ttd));

        $date = substr($activity->start_date, 5, 2);
        $date .= substr($activity->start_date, 8, 2);
        $data['number'] = $date . "/UN1/FKKMK.2/JP.1/AK/" . date('Y');
        $data['type'] = $activity->category === 7 ? 'Seminar Kasus' : 'Referat';

        $data['activity'] = $activity;
        $data['date'] = substr($activity->start_date, 8, 2) . ' ';
        $data['date'] .= $this->bulan((int)substr($activity->start_date, 5, 2)) . ' ';
        $data['date'] .= substr($activity->start_date, 0, 4);
        $data['day'] = $this->hari(date('w', strtotime($activity->start_date)));

        $lecture_ids = json_decode($data['activity']['lecture_pembimbing']);
        $lecture_ids = array_merge(json_decode($data['activity']['lecture_penguji']), $lecture_ids);
        $lecture_ids = array_merge(json_decode($data['activity']['lecture_pengampu']), $lecture_ids);

        $data['all_staff'] = $this->all_staff($lecture_ids);
        return view('templates.pdf.undangan_referat', compact(
            'logo',
            'data',
            'ttd'
        ));

        $pdf = PDF::loadView('templates.pdf.sk_referat', compact(
            'data',
            'logo',
            'ttd'
        ))
            ->setPaper('a4');

        return $pdf->download('Undangan ' . $activity->name . ' ' . $data['number'] . '.pdf');
    }

    private function bulan($i)
    {
        $bulan = [
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
        ];

        return $bulan[$i - 1];
    }

    private function hari($i)
    {
        $data = [
            'Minggu',
            'Senin',
            'Selasa',
            'Rabu',
            'Kamis',
            'Jumat',
            'Sabtu',
        ];

        return $data[$i];
    }

    private function all_staff($main_ids = []){
        $container = [
            'Ketua Departemen Kardiologi dan Kedokteran Vaskular',
            'Ketua Program Studi Jantung dan Pembuluh Darah',
        ];
        $main_name = Lecture::whereIn('id', $main_ids)->pluck('name_alt')->toArray();
        $main_name_append = [];
        foreach ($main_name as $name){
            $main_name_append[] = $name . ' (Pembimbing)';
        }

        $all_staff = Lecture::whereNotIn('id', $main_ids)->where('is_in_house', '=',1)->pluck('name_alt')->toArray();

        $container = array_merge($main_name_append, $container);
        $container = array_merge($container, $all_staff);
        return $container;
    }

    public function logbook_stase(Request $request){
        $stase = Stase::find($request->stase_id);

        if(!$stase){
            return 'no stase';
        }

        $logbook = StudentLog::whereIn('stase_id', [19,5])
            ->when($request->lecture_id, function ($q) use ($request){
                $q->whereLectureId($request->lecture_id);
            })
            ->with(['student', 'stase', 'lecture'])
            ->whereDate('date', '>', '2020-12-31')
            ->whereDate('date', '<', '2023-01-01')
            ->orderBy('date')
//            ->limit(10)
            ->get();

        $data['dataContent'] = $logbook;
        $data['query'] = $request->all();
        $data['template'] = 'templates.excel.logbook_lecture';

//        return view('templates.pdf.logbook_stase', compact('logbook', 'stase'));
//        return view($data['template'], $data);
        return Excel::download(new DefaultExport($data), 'Logbook ' . $request->year .'.xls');
    }

    public function agenda_by_lecture(Request $request){
        $data_content = Activity::where(function ($q) use ($request){
            $q->where('lecture_pembimbing', 'LIKE', '%'.$request->lecture_id.','.'%')
                ->orWhere('lecture_pembimbing', 'LIKE', '%'.$request->lecture_id.']'.'%')
                ->orWhere('lecture_penguji', 'LIKE', '%'.$request->lecture_id.','.'%')
                ->orWhere('lecture_penguji', 'LIKE', '%'.$request->lecture_id.']'.'%')
                ->orWhere('lecture_pengampu', 'LIKE', '%'.$request->lecture_id.','.'%')
                ->orWhere('lecture_pengampu', 'LIKE', '%'.$request->lecture_id.']'.'%');
        })
//            ->limit(10)
            ->get();
//        return $data_content;

        return view('templates.excel.activity_by_lecture', [
            'data_content' => $data_content
        ]);
    }
}
