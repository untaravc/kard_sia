<?php

namespace App\Http\Controllers\Object;

use App\Http\Controllers\Controller;
use App\Imports\QuizImport;
use App\Models\Object\Quiz;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    const NUMBER = 0;
    const BOOK = 1;
    const CATEGORY = 2;
    const HAS_IMG = 3;
    const QUESTION = 4;
    const OPTION_1 = 5;
    const OPTION_2 = 6;
    const OPTION_3 = 7;
    const OPTION_4 = 8;
    const OPTION_5 = 9;
    const OPTION_6 = 10;
    const ANSWER = 11;
    const DISCUSSION = 12;
    const CAT_LIST = [
        'cardiac anatomy and physiology',
        'congenital cardiac malformations',
        'diagnosis of congenital heart disease',
        'cardiac catheterization and angiography',
        'noninvasive cardiac imaging',
        'electrophysiology questions for pediatric cardiology',
        'physiology and testing',
        'outpatient cardiology',
        'cardiac intensive care and heart failure',
        'cardiac pharmacology',
        'surgical palliation and repair of congenital heart disease',
        'adult congenital heart disease',
        'statistics and epidemiology',
    ];
    const BOOK_LIST = [
        'pediatric cardiology board review'
    ];

    public function import(Request $request)
    {
        $rows = $this->active_rows($request);
        $this->response['status'] = false;
        if (count($rows) < 1) {
            $this->response['errors'] = "data tidak ditemukan.";
            return $this->response;
        }

        // Validasi
        $errors = $this->validateData($rows);

        if (count($errors) > 0) {
            $this->response['errors'] = $errors;
            return $this->response;
        }

        // Input Data
        $this->inputData($rows);

        $this->response['status'] = true;
        $this->response['message'] = 'Berhasil upload ' . count($rows) . ' data.';
        return $this->response;
    }

    private function active_rows(Request $request)
    {
        $tabs = Excel::toArray(new QuizImport, $request->file);
        $rows = $tabs[0];

        $active_rows = [];
        foreach ($rows as $cols) {
            if ($cols[0] > 0) {
                $active_rows[] = $cols;
            }
        }
        return $active_rows;
    }

    private function validateData($rows)
    {
        $errors = [];
        foreach ($rows as $col) {
            if (!is_numeric($col[self::NUMBER])) {
                $errors[] = 'Nomor soal nomor ' . $col[0] . ' harus diisi. -' . $col[1];
            }

            if (strlen($col[self::BOOK]) < 1) {
                $errors[] = 'Buku nomor ' . $col[0] . ' harus diisi. -' . $col[1];
            }

            if (!in_array(strtolower($col[self::BOOK]), self::BOOK_LIST)) {
                $errors[] = 'Buku nomor ' . $col[0] . ' belum terdaftar. -' . $col[1];
            }

            if (strlen($col[self::CATEGORY]) < 1) {
                $errors[] = 'Kategori nomor ' . $col[0] . ' tidak sesuai. -' . $col[1];
            }

            if (!in_array(strtolower($col[self::CATEGORY]), self::CAT_LIST)) {
                $errors[] = 'Kategori nomor ' . $col[self::NUMBER] . ' belum terdaftar. -' . strtolower($col[self::CATEGORY]);
            }

            if (!in_array(strtolower($col[self::HAS_IMG]), ['tidak', 'ada'])) {
                $errors[] = 'Ket gambar nomor ' . $col[0] . ' harus diisi (tidak, ada). -' . $col[1];
            }

            if (strlen($col[self::QUESTION]) < 10) {
                $errors[] = 'Pertanyaan nomor ' . $col[0] . ' tidak sesuai. -' . $col[1];
            }

            if (strlen($col[self::OPTION_1]) < 1) {
                $errors[] = 'JAWABAN 1 nomor ' . $col[0] . ' tidak sesuai. -' . $col[self::CATEGORY];
            }

            if (strlen($col[self::OPTION_2]) < 1) {
                $errors[] = 'JAWABAN 2 nomor ' . $col[0] . ' tidak sesuai. -' . $col[self::CATEGORY];
            }

            if (strlen($col[self::OPTION_3]) < 1) {
                $errors[] = 'JAWABAN 3 nomor ' . $col[0] . ' tidak sesuai. -' . $col[self::CATEGORY];
            }

            if (!in_array(strtolower($col[self::ANSWER]), [
                'option_1',
                'option_2',
                'option_3',
                'option_4',
                'option_5',
                'option_6',
            ])) {
                $errors[] = 'Jawaban nomor ' . $col[0] . ' tidak sesuai. -' . $col[1];
            }

            if (strlen($col[self::DISCUSSION]) < 1) {
                $errors[] = 'Diskusi nomor ' . $col[0] . ' tidak sesuai. -' . $col[1];
            }
        }

        return $errors;
    }

    private function inputData($rows)
    {
        foreach ($rows as $cols) {
            $quiz = Quiz::whereBook($cols[self::BOOK])
                ->whereCategory($cols[self::CATEGORY])
                ->whereBookNumber($cols[self::NUMBER])
                ->first();

            if ($quiz) {
                $quiz->update([
                    'has_img'        => strtolower($cols[self::HAS_IMG]) == 'tidak' ? 0 : 1,
                    'question'       => $cols[self::QUESTION],
                    'question_image' => null,
                    'option_1'       => $cols[self::OPTION_1],
                    'option_2'       => $cols[self::OPTION_2],
                    'option_3'       => $cols[self::OPTION_3],
                    'option_4'       => $cols[self::OPTION_4],
                    'option_5'       => $cols[self::OPTION_5],
                    'option_6'       => $cols[self::OPTION_6],
                    'answer'         => $cols[self::ANSWER],
                    'score'          => 1,
                    'note'           => $cols[self::DISCUSSION],
                    'category'       => strtolower($cols[self::CATEGORY]),
                    'status'         => strtolower($cols[self::HAS_IMG]) == 'tidak' ? 100 : 110,
                ]);
            } else {
                Quiz::create([
                    'book'           => strtolower($cols[self::BOOK]),
                    'book_number'    => $cols[self::NUMBER],
                    'category'       => strtolower($cols[self::CATEGORY]),
                    'image'          => null,
                    'has_img'        => strtolower($cols[self::HAS_IMG]) == 'tidak' ? 0 : 1,
                    'question'       => $cols[self::QUESTION],
                    'question_image' => null,
                    'option_1'       => $cols[self::OPTION_1],
                    'option_2'       => $cols[self::OPTION_2],
                    'option_3'       => $cols[self::OPTION_3],
                    'option_4'       => $cols[self::OPTION_4],
                    'option_5'       => $cols[self::OPTION_5],
                    'option_6'       => $cols[self::OPTION_6],
                    'answer'         => $cols[self::ANSWER],
                    'score'          => 1,
                    'note'           => $cols[self::DISCUSSION],
                    'status'         => strtolower($cols[self::HAS_IMG]) == 'tidak' ? 100 : 110,
                ]);
            }
        }
    }
}
