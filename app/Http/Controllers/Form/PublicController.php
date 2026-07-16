<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormAnswer;
use App\Models\FormField;
use App\Models\FormResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicController extends Controller
{
    /**
     * Render the public fill page for a form identified by its slug.
     */
    public function show($slug)
    {
        $form = Form::with('fields')->where('slug', $slug)->first();
        if (!$form) {
            abort(404);
        }

        $auth = $this->resolveRespondent();

        // Draft / closed forms are not open for submission.
        if ((int) $form->status !== 1) {
            $message = (int) $form->status === 2
                ? 'Formulir ini sudah ditutup.'
                : 'Formulir ini belum dipublikasikan.';
            return view('form.closed', compact('form', 'message'));
        }

        // Login required forms redirect guests to a prompt.
        if ($form->visibility === 'auth' && !$auth) {
            return view('form.login_required', compact('form'));
        }

        return view('form.fill', compact('form', 'auth'));
    }

    /**
     * Persist a submission and its answers.
     */
    public function submit(Request $request, $slug)
    {
        $form = Form::with('fields')->where('slug', $slug)->firstOrFail();

        if ((int) $form->status !== 1) {
            abort(403, 'Formulir tidak menerima respon.');
        }

        $auth = $this->resolveRespondent();
        if ($form->visibility === 'auth' && !$auth) {
            abort(403, 'Anda harus login untuk mengisi formulir ini.');
        }

        // Build validation rules from the field definitions.
        $rules = [];
        $attributes = [];
        foreach ($form->fields as $field) {
            $key = 'field_' . $field->id;
            $rule = [];
            $rule[] = $field->is_required ? 'required' : 'nullable';

            switch ($field->type) {
                case 'email':
                    $rule[] = 'email';
                    break;
                case 'number':
                    $rule[] = 'numeric';
                    break;
                case 'date':
                    $rule[] = 'date';
                    break;
                case 'checkbox':
                    $rule[] = 'array';
                    break;
                case 'rating':
                    $rule[] = 'integer';
                    break;
            }

            $rules[$key] = implode('|', $rule);
            $attributes[$key] = $field->label;
        }

        // Guest identity capture when nobody is logged in.
        if (!$auth) {
            $rules['respondent_name'] = 'required';
            $attributes['respondent_name'] = 'Nama';
            if ($form->collect_email) {
                $rules['respondent_email'] = 'required|email';
                $attributes['respondent_email'] = 'Email';
            }
        }

        $request->validate($rules, [], $attributes);

        $response = FormResponse::create([
            'form_id'          => $form->id,
            'respondent_type'  => $auth ? $auth['type'] : 'guest',
            'respondent_id'    => $auth ? $auth['id'] : null,
            'respondent_name'  => $auth ? $auth['name'] : $request->input('respondent_name'),
            'respondent_email' => $auth ? $auth['email'] : $request->input('respondent_email'),
            'ip_address'       => $request->ip(),
            'user_agent'       => substr((string) $request->userAgent(), 0, 255),
        ]);

        foreach ($form->fields as $field) {
            $value = $request->input('field_' . $field->id);

            if (is_array($value)) {
                // Checkbox (multiple) answers stored as JSON.
                $value = json_encode(array_values($value));
            }

            FormAnswer::create([
                'form_response_id' => $response->id,
                'form_field_id'    => $field->id,
                'value'            => $value,
            ]);
        }

        return redirect('/form/' . $form->slug . '/thanks');
    }

    public function thanks($slug)
    {
        $form = Form::where('slug', $slug)->firstOrFail();
        return view('form.thanks', compact('form'));
    }

    /**
     * Detect the currently authenticated respondent across the app's guards.
     * Returns null when nobody is logged in (guest).
     */
    protected function resolveRespondent()
    {
        $guards = [
            'student' => 'student',
            'lecture' => 'lecture',
            'web'     => 'user',
        ];

        foreach ($guards as $guard => $type) {
            $user = Auth::guard($guard)->user();
            if ($user) {
                return [
                    'type'  => $type,
                    'id'    => $user->id,
                    'name'  => $user->name ?? ($user->email ?? ''),
                    'email' => $user->email ?? null,
                ];
            }
        }

        return null;
    }
}
