<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ActionLogStore;
use Illuminate\Http\Request;

class ActionLogController extends Controller
{
    protected $store;

    public function __construct(ActionLogStore $store)
    {
        $this->store = $store;
    }

    public function index(Request $request)
    {
        $date = $request->get('date') ?: now()->format('Y-m-d');

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Action Logs Success',
            'result' => [
                'date' => $date,
                'data' => $this->store->readForDate($date),
            ],
        ]);
    }

    public function cleanup(Request $request)
    {
        $this->validate($request, [
            'days' => 'required|integer|in:7,30',
        ]);

        $days = (int) $request->get('days');
        $deleted = $this->store->deleteOlderThan($days);

        return response()->json([
            'success' => true,
            'text' => "Deleted logs older than {$days} days",
            'result' => ['deleted_files' => $deleted],
        ]);
    }
}
