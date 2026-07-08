<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MailLog;
use Illuminate\Http\Request;

class MailLogController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = MailLog::query()->latest();
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Mail Logs Success',
            'result' => $dataContent,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $mailLog = MailLog::create($data);

        return response()->json([
            'success' => true,
            'text' => 'Create Mail Log Success',
            'result' => $mailLog,
        ]);
    }

    public function show($id)
    {
        $mailLog = MailLog::find($id);
        if (!$mailLog) {
            return response()->json([
                'success' => false,
                'text' => 'Mail Log not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Mail Log Success',
            'result' => $mailLog,
        ]);
    }

    public function update(Request $request, $id)
    {
        $mailLog = MailLog::find($id);
        if (!$mailLog) {
            return response()->json([
                'success' => false,
                'text' => 'Mail Log not found',
                'result' => null,
            ], 404);
        }

        $data = $this->validateData($request);
        $mailLog->update($data);

        return response()->json([
            'success' => true,
            'text' => 'Update Mail Log Success',
            'result' => $mailLog,
        ]);
    }

    public function destroy($id)
    {
        $mailLog = MailLog::find($id);
        if (!$mailLog) {
            return response()->json([
                'success' => false,
                'text' => 'Mail Log not found',
                'result' => null,
            ], 404);
        }

        $mailLog->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Mail Log Success',
            'result' => null,
        ]);
    }

    protected function validateData(Request $request)
    {
        return $this->validate($request, [
            'name' => 'nullable|string',
            'destination_email' => 'nullable|string',
            'destination_phone' => 'nullable|string',
            'origin_email' => 'nullable|string',
            'title' => 'nullable|string',
            'template' => 'nullable|string',
            'label' => 'nullable|string',
            'document_url' => 'nullable|string',
            'data' => 'nullable',
            'status' => 'nullable|string',
            'sent_at' => 'nullable|date',
        ]);
    }

    protected function withFilter($dataContent, Request $request)
    {
        if ($request->filled('keyword')) {
            $dataContent = $dataContent->where(function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('destination_email', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('title', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('label', 'LIKE', '%' . $request->keyword . '%');
            });
        }

        if ($request->filled('status')) {
            $dataContent = $dataContent->where('status', $request->status);
        }

        if ($request->filled('label')) {
            $dataContent = $dataContent->where('label', $request->label);
        }

        if ($request->filled('template')) {
            $dataContent = $dataContent->where('template', $request->template);
        }

        if ($request->filled('destination_email')) {
            $dataContent = $dataContent->where('destination_email', $request->destination_email);
        }

        return $dataContent;
    }
}
