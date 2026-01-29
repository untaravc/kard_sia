<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityLecture;
use App\Models\ActivityStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = Activity::orderByDesc('start_date');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Activities Success',
            'result' => $dataContent,
        ]);
    }

    public function store(Request $request)
    {
        $this->validateData($request);

        $payload = $request->all();
        if (!array_key_exists('created_by', $payload)) {
            $payload['created_by'] = 0;
        }
        if (!array_key_exists('status', $payload)) {
            $payload['status'] = 'active';
        }

        $activity = Activity::create($payload);

        return response()->json([
            'success' => true,
            'text' => 'Create Activity Success',
            'result' => $activity,
        ]);
    }

    public function show($id)
    {
        $activity = Activity::find($id);

        if (!$activity) {
            return response()->json([
                'success' => false,
                'text' => 'Activity not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Activity Success',
            'result' => $activity,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);

        $activity = Activity::find($id);
        if (!$activity) {
            return response()->json([
                'success' => false,
                'text' => 'Activity not found',
                'result' => null,
            ], 404);
        }

        $activity->update($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Update Activity Success',
            'result' => $activity,
        ]);
    }

    public function destroy($id)
    {
        $activity = Activity::find($id);
        if (!$activity) {
            return response()->json([
                'success' => false,
                'text' => 'Activity not found',
                'result' => null,
            ], 404);
        }

        $activity->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Activity Success',
            'result' => null,
        ]);
    }

    public function activitiesToday(Request $request)
    {
        $dateParam = $request->query('date');
        try {
            $today = $dateParam ? Carbon::parse($dateParam)->startOfDay() : Carbon::today();
        } catch (\Exception $e) {
            $today = Carbon::today();
        }

        $activities = Activity::whereDate('start_date', '=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $today);
            })
            ->orderBy('start_date')
            ->get();

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Today Activities Success',
            'result' => $activities,
        ]);
    }

    public function presence(Request $request, $activity_id)
    {
        $payload = $request->attributes->get('jwt_payload');
        $authType = $payload ? data_get($payload, 'log_as_auth_type') : null;
        if (!$authType) {
            $authType = $payload ? data_get($payload, 'auth_type') : null;
        }
        $authId = $payload ? data_get($payload, 'log_as_auth_id') : null;
        if (!$authId) {
            $authId = $payload ? data_get($payload, 'auth_id') : null;
        }

        if (!$authType || !$authId) {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 401);
        }

        $activity = Activity::find($activity_id);
        if (!$activity) {
            return response()->json([
                'success' => false,
                'text' => 'Activity not found',
                'result' => null,
            ], 404);
        }

        $presence = null;
        if ($authType === 'student') {
            $presence = ActivityStudent::firstOrCreate([
                'activity_id' => $activity_id,
                'student_id' => $authId,
            ]);
        } else if ($authType === 'lecture') {
            $presence = ActivityLecture::firstOrCreate([
                'activity_id' => $activity_id,
                'lecture_id' => $authId,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 401);
        }

        return response()->json([
            'success' => true,
            'text' => 'Presence recorded',
            'result' => $presence,
        ]);
    }

    private function validateData(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);
    }

    private function withFilter($dataContent, Request $request)
    {
        if ($request->keyword != null) {
            $keyword = $request->keyword;
            $dataContent = $dataContent->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('speaker', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('title', 'LIKE', '%' . $keyword . '%');
            });
        }

        return $dataContent;
    }
}
