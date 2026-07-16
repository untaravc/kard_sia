<?php

namespace App\Http\Controllers\Api\Form;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormResponse;
use Illuminate\Http\Request;

class ResponseController extends Controller
{
    /**
     * List responses for a form together with its field definitions so the
     * admin table can render one column per question.
     */
    public function index(Request $request, $formId)
    {
        $form = Form::with('fields')->find($formId);
        if (!$form) {
            return response()->json([
                'success' => false,
                'text'    => 'Form not found',
                'result'  => null,
            ], 404);
        }

        $responses = FormResponse::with('answers')
            ->where('form_id', $formId)
            ->orderByDesc('id')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'text'    => 'Retrieve Responses Success',
            'result'  => $responses,
            'form'    => $form,
        ]);
    }

    public function show($id)
    {
        $response = FormResponse::with(['answers.field', 'form'])->find($id);
        if (!$response) {
            return response()->json([
                'success' => false,
                'text'    => 'Response not found',
                'result'  => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text'    => 'Retrieve Response Success',
            'result'  => $response,
        ]);
    }

    public function destroy($id)
    {
        $response = FormResponse::find($id);
        if (!$response) {
            return response()->json([
                'success' => false,
                'text'    => 'Response not found',
                'result'  => null,
            ], 404);
        }

        $response->answers()->delete();
        $response->delete();

        return response()->json([
            'success' => true,
            'text'    => 'Delete Response Success',
            'result'  => null,
        ]);
    }
}
