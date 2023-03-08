<?php

namespace App\Http\Controllers\Api\Dosen;

use App\Http\Controllers\Controller;
use App\Models\StaseTaskLog;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index() {
        $lecture = auth_dosen();
        $data = StaseTaskLog::with([
            'stase',
            'staseTask',
            'files',
            'student',
        ])->whereLectureId($lecture['id'])
            ->orderByDesc('created_at')
            ->paginate(10);

        $this->res['status'] = true;
        $this->res['text'] = 'Retrieve history data success';
        $this->res['data'] = [
            'history' => $data
        ];

        return $this->res;
    }
}
