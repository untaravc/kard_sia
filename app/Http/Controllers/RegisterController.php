<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\RegistrationDetail;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function print($registration_id)
    {
        $registration = Registration::where('id', $registration_id)->first();
        if (!$registration) {
            abort(404, 'Registration not found');
        }

        $details = RegistrationDetail::where('registration_id', $registration_id)->get();

        $children = $details->where('label', 'child')->values();
        $educations = $details->where('label', 'education')->values();
        $jobs = $details->where('label', 'job')->values();
        $scientifics = $details->where('label', 'scientific')->values();
        $organisations = $details->where('label', 'organisation')->values();
        $achievements = $details->where('label', 'achievement')->values();
        $recommendations = $details->where('label', 'recommendation')->values();
        $scores = $details->where('label', 'score')->values();

        return view('registration.print', compact(
            'registration',
            'children',
            'educations',
            'jobs',
            'scientifics',
            'organisations',
            'achievements',
            'recommendations',
            'scores'
        ));
    }

    public function resumeView()
    {
        return view('registration.resume');
    }

    public function registrationProfiles(Request $request)
    {
        $period = $request->get('registration_period', env('REGISTRATION_PERIOD'));

        $registrations = Registration::when(!empty($period), function ($query) use ($period) {
            $query->where('registration_period', $period);
        })
            ->orderBy('name')
            ->get();

        $details = RegistrationDetail::whereIn('registration_id', $registrations->pluck('id')->all())->get();

        foreach ($registrations as $registration) {
            $registration->setAttribute('children', $details->where('registration_id', $registration->id)->where('label', 'child')->values());
            $registration->setAttribute('educations', $details->where('registration_id', $registration->id)->where('label', 'education')->values());
            $registration->setAttribute('jobs', $details->where('registration_id', $registration->id)->where('label', 'job')->values());
            $registration->setAttribute('scientifics', $details->where('registration_id', $registration->id)->where('label', 'scientific')->values());
            $registration->setAttribute('organisations', $details->where('registration_id', $registration->id)->where('label', 'organisation')->values());
            $registration->setAttribute('achievements', $details->where('registration_id', $registration->id)->where('label', 'achievement')->values());
            $registration->setAttribute('recommendations', $details->where('registration_id', $registration->id)->where('label', 'recommendation')->values());
            $registration->setAttribute('scores', $details->where('registration_id', $registration->id)->where('label', 'score')->values());
        }

        return view('registration.print-all', compact('registrations', 'period'));
    }
}
