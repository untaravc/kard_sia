<?php

namespace App\Http\Controllers;

use App\Models\FormOption;
use App\Models\OpenStaseTask;
use App\Models\Presence;
use App\Models\StandardPoint;
use App\Models\StaseLog;
use App\Models\Student;
use App\Models\StudentLog;
use http\Client;

class TestController extends Controller
{
    public function test()
    {
        $standard_point = StandardPoint::with('form_options')->get();
        $student_log = StudentLog::whereStudentId(75)->get();

        foreach ($standard_point as $point){
            $stase_ids = (collect($point['form_options'])->unique('relation_id')->pluck('relation_id')->toArray());
            $types = (collect($point['form_options'])->unique('value')->pluck('value')->toArray());

            $point->setAttribute('achieves', $student_log->whereIn('stase_id', $stase_ids )
                ->whereIn('type', $types)->count());
        }

        return $standard_point;
    }

    public function get_form_option(){
        return FormOption::with('stase')->orderBy('relation_id')
            ->where('type', 'stase-logbook')->pluck('relation_id');
    }

    public function upload_form(){
        return view('test_pages.upload_form');
    }

    public function send_message(){
        $url = "https://messages-sandbox.nexmo.com/v0.1/messages";
        $params = ["to" => ["type" => "whatsapp", "number" => 'number'],
                   "from" => ["type" => "whatsapp", "number" => "14157386170"],
                   "message" => [
                       "content" => [
                           "type" => "text",
                           "text" => "Hello from Vonage and Laravel :) Please reply to this message with a number between 1 and 100"
                       ]
                   ]
        ];
        $headers = ["Authorization" => "Basic " . base64_encode(env('NEXMO_API_KEY') . ":" . env('NEXMO_API_SECRET'))];

//        $client = new Client
//        $response = $client->request('POST', $url, ["headers" => $headers, "json" => $params]);
//        $data = $response->getBody();

        return view('thanks');
    }


}
