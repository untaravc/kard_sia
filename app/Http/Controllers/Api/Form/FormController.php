<?php

namespace App\Http\Controllers\Api\Form;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FormController extends Controller
{
    /**
     * Paginated list of forms plus dashboard counters, consumed by the
     * js2 admin SPA.
     */
    public function index(Request $request)
    {
        $query = Form::withCount(['fields', 'responses'])->orderByDesc('id');

        if ($request->filled('keyword')) {
            $query->where('title', 'LIKE', '%' . $request->keyword . '%');
        }
        if ($request->filled('visibility')) {
            $query->where('visibility', $request->visibility);
        }
        if ($request->filled('status') || $request->status === '0' || $request->status === 0) {
            $query->where('status', (int) $request->status);
        }

        $result = $query->paginate(10);

        return response()->json([
            'success' => true,
            'text'    => 'Retrieve Forms Success',
            'result'  => $result,
            'stats'   => [
                'draft'     => Form::whereStatus(0)->count(),
                'published' => Form::whereStatus(1)->count(),
                'closed'    => Form::whereStatus(2)->count(),
                'responses' => FormResponse::count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        if ($error = $this->validationError($request)) {
            return $error;
        }

        $form = Form::create($this->formPayload($request));
        $this->syncFields($form, $request->input('fields', []));

        return response()->json([
            'success' => true,
            'text'    => 'Create Form Success',
            'result'  => $form->load('fields'),
        ]);
    }

    public function show($id)
    {
        $form = Form::with('fields')->find($id);
        if (!$form) {
            return $this->notFound();
        }

        return response()->json([
            'success' => true,
            'text'    => 'Retrieve Form Success',
            'result'  => $form,
        ]);
    }

    public function update(Request $request, $id)
    {
        $form = Form::find($id);
        if (!$form) {
            return $this->notFound();
        }

        if ($error = $this->validationError($request)) {
            return $error;
        }

        $form->update($this->formPayload($request, $form->id));
        $this->syncFields($form, $request->input('fields', []));

        return response()->json([
            'success' => true,
            'text'    => 'Update Form Success',
            'result'  => $form->load('fields'),
        ]);
    }

    public function destroy($id)
    {
        $form = Form::find($id);
        if (!$form) {
            return $this->notFound();
        }

        // Soft delete: the form is hidden but its fields and responses are
        // kept intact so it can be restored later.
        $form->delete();

        return response()->json([
            'success' => true,
            'text'    => 'Delete Form Success',
            'result'  => null,
        ]);
    }

    protected function notFound()
    {
        return response()->json([
            'success' => false,
            'text'    => 'Form not found',
            'result'  => null,
        ], 404);
    }

    /**
     * Returns a 422 json response when validation fails, otherwise null.
     */
    protected function validationError(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'      => 'required',
            'visibility' => 'required|in:public,auth',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'text'    => $validator->errors()->first(),
                'result'  => null,
            ], 422);
        }

        return null;
    }

    protected function formPayload(Request $request, $ignoreId = null)
    {
        $payload = [
            'title'          => $request->title,
            'slug'           => $this->uniqueSlug($request->slug ?: $request->title, $ignoreId),
            'description'    => $request->description,
            'visibility'     => $request->visibility ?: 'public',
            'status'         => $request->status !== null ? (int) $request->status : 1,
            'allow_multiple' => $request->boolean('allow_multiple') ? 1 : 0,
            'collect_email'  => $request->boolean('collect_email') ? 1 : 0,
        ];

        if ($ignoreId === null) {
            $jwt = $request->attributes->get('jwt_payload');
            $payload['created_by']      = $jwt ? data_get($jwt, 'auth_id') : null;
            $payload['created_by_type'] = $jwt ? data_get($jwt, 'auth_type') : null;
        }

        return $payload;
    }

    /**
     * Build a unique url friendly slug; keeps the form's own slug on edit.
     */
    protected function uniqueSlug($value, $ignoreId = null)
    {
        $base = Str::slug($value);
        if ($base === '') {
            $base = 'form';
        }

        $slug = $base;
        $i = 1;
        while (true) {
            $query = Form::where('slug', $slug);
            if ($ignoreId !== null) {
                $query->where('id', '!=', $ignoreId);
            }
            if (!$query->exists()) {
                break;
            }
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    /**
     * Upsert incoming fields against the form: match existing by id (updated),
     * create new ones, and delete any the admin removed. Ids are preserved so
     * previously collected answers stay linked to their questions.
     */
    protected function syncFields(Form $form, $fields)
    {
        if (!is_array($fields)) {
            $fields = [];
        }

        $keepIds = [];

        foreach (array_values($fields) as $i => $field) {
            $type = isset($field['type']) ? $field['type'] : 'text';

            $options = null;
            if (in_array($type, FormField::CHOICE_TYPES)) {
                $raw = isset($field['options']) && is_array($field['options']) ? $field['options'] : [];
                $options = array_values(array_filter($raw, function ($o) {
                    return $o !== null && trim((string) $o) !== '';
                }));
            }

            $data = [
                'type'        => $type,
                'label'       => isset($field['label']) ? $field['label'] : '',
                'description' => isset($field['description']) ? $field['description'] : null,
                'placeholder' => isset($field['placeholder']) ? $field['placeholder'] : null,
                'options'     => $options,
                'is_required' => !empty($field['is_required']) ? 1 : 0,
                'max_rating'  => $type === 'rating' ? (int) (!empty($field['max_rating']) ? $field['max_rating'] : 5) : null,
                'position'    => $i,
            ];

            $existing = null;
            if (!empty($field['id'])) {
                $existing = FormField::where('form_id', $form->id)->where('id', $field['id'])->first();
            }

            if ($existing) {
                $existing->update($data);
                $keepIds[] = $existing->id;
            } else {
                $created = $form->fields()->create($data);
                $keepIds[] = $created->id;
            }
        }

        $form->fields()->whereNotIn('id', $keepIds ?: [0])->delete();
    }
}
