<?php

namespace App\Http\Controllers;

use App\Models\OpenStaseTask;
use App\Models\Presence;
use App\Models\Student;

class TestController extends Controller
{
    public function test()
    {
        OpenStaseTask::withTrashed()->whereHas('files')
            ->where('deleted_at', '!=', null)
            ->update([
                'deleted_at' => null
            ]);
    }
}
