<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lecture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request) {
        $this->validate($request, [
           'email' => 'required|email',
           'password' => 'required',
        ]);

        $dosen = Lecture::whereEmail($request->email)->first();
        if($dosen){
            if(Hash::check($request->password, $dosen->password)){
                $this->response['status'] = true;
                $this->response['text'] = 'Login Success';
                $this->response['data'] = [
                    'token' => $dosen->token,
                ];
            }
        }
        return $this->response;
    }

    public function profile() {
        $dosen = auth_dosen();
        if($dosen){
            $this->response['status'] = true;
            $this->response['text'] = 'Retrieve Profile Success';
            $this->response['data'] = [
                'profile' => $dosen,
            ];;
        }
        return $this->response;
    }

    public function logout() {
        $this->response['status'] = true;
        $this->response['text'] = 'Logged Out';
        return $this->response;
    }

    public function menu(Request $request) {
        $basePath = $request->get('basePath', '/cblu');

        $menu = [
            [ 'label' => 'Dashboard', 'icon' => 'dashboard', 'to' => "{$basePath}/dashboard" ],
            [
                'label' => 'Dosen',
                'icon' => 'dosen',
                'children' => [
                    [ 'label' => 'Data', 'to' => "{$basePath}/lecture" ],
                    [ 'label' => 'Dokumen', 'to' => "{$basePath}/lecture-file" ],
                ],
            ],
            [
                'label' => 'Residen',
                'icon' => 'resident',
                'children' => [
                    [ 'label' => 'Data', 'to' => "{$basePath}/resident" ],
                    [ 'label' => 'Presensi', 'to' => "{$basePath}/presences" ],
                    [ 'label' => 'Presensi Harian', 'to' => "{$basePath}/presence-resume" ],
                    [ 'label' => 'Resume Presensi', 'to' => "{$basePath}/presence-ilmiah-resume" ],
                    [ 'label' => 'Stase Residen', 'to' => "{$basePath}/stase-plots" ],
                    [ 'label' => 'Ujian', 'to' => "{$basePath}/exams" ],
                    [ 'label' => 'Dokumen', 'to' => "{$basePath}/letters" ],
                    [ 'label' => 'Log Book', 'to' => "{$basePath}/student-logs" ],
                    [ 'label' => 'Download', 'to' => "{$basePath}/download" ],
                    [ 'label' => 'Calon PPDS', 'to' => "{$basePath}/student-regs" ],
                    [ 'label' => 'Pengabdian Masyarakat', 'to' => "{$basePath}/document-review" ],
                    [ 'label' => 'Laporan Harian', 'to' => "{$basePath}/daily-report" ],
                ],
            ],
            [
                'label' => 'CBT',
                'icon' => 'cbt',
                'children' => [
                    [ 'label' => 'Kategori', 'to' => '/object/categories' ],
                    [ 'label' => 'Bank Soal', 'to' => '/object/quizzes' ],
                    [ 'label' => 'Paket Ujian', 'to' => '/object/quiz-sections' ],
                ],
            ],
            [ 'label' => 'Ujian Terbuka', 'icon' => 'open-exam', 'to' => "{$basePath}/open-exam" ],
            [ 'label' => 'Agenda', 'icon' => 'agenda', 'to' => "{$basePath}/activity" ],
            [
                'label' => 'Data Master',
                'icon' => 'data-master',
                'children' => [
                    [ 'label' => 'Surat', 'to' => "{$basePath}/letter-records" ],
                    [ 'label' => 'Stase', 'to' => "{$basePath}/stase" ],
                    [ 'label' => 'Task', 'to' => "{$basePath}/task" ],
                    [ 'label' => 'Admin', 'to' => "{$basePath}/user" ],
                ],
            ],
        ];

        $this->response['status'] = true;
        $this->response['text'] = 'Retrieve Menu Success';
        $this->response['data'] = [
            'menu' => $menu,
        ];

        return $this->response;
    }
}
