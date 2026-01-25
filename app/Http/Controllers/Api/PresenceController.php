<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Presence;
use Illuminate\Http\Request;

class PresenceController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = Presence::with('student')->orderByDesc('checkin');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Presences Success',
            'result' => $dataContent,
        ]);
    }

    public function withFilter($dataContent, $request)
    {
        if ($request->date != null && $request->date !== '') {
            $dataContent = $dataContent->whereDate('checkin', $request->date);
        }

        if ($request->name != null && $request->name !== '') {
            $dataContent = $dataContent->whereHas('student', function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->name . '%');
            });
        }

        return $dataContent;
    }
}
