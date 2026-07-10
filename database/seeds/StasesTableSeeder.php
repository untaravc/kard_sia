<?php

use Illuminate\Database\Seeder;

class StasesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $stases = [
            ['id' => 1, 'name' => 'Bangsal IPD', 'alias' => 'IPD.WARD', 'stase_order' => 1, 'is_mandatory' => 1, 'color' => '#f5a700', 'font_color' => '#121212'],
            ['id' => 2, 'name' => 'Ilmu Penyakit Dalam', 'alias' => 'IPD', 'stase_order' => 2, 'is_mandatory' => 1, 'color' => '#0e010e', 'font_color' => '#f7f3f3'],
            ['id' => 3, 'name' => 'Ilmu Kesehatan Anak', 'alias' => 'IK.Anak', 'stase_order' => 3, 'is_mandatory' => 1, 'color' => '#b42222', 'font_color' => '#ffffff'],
            ['id' => 4, 'name' => 'Kardiovaskular Klinik Dewasa 1', 'alias' => 'KKV 1', 'stase_order' => 4, 'is_mandatory' => 1, 'color' => '#0d44e7', 'font_color' => '#f2f2f2'],
            ['id' => 5, 'name' => 'Ekhokardiografi 1', 'alias' => 'Echo 1', 'stase_order' => 5, 'is_mandatory' => 1, 'color' => '#fcff57', 'font_color' => '#0f0f0f'],
            ['id' => 6, 'name' => 'Kardiologi Intervensi', 'alias' => 'Invasif', 'stase_order' => 6, 'is_mandatory' => 1, 'color' => '#ff0000', 'font_color' => '#faf5f5'],
            ['id' => 7, 'name' => 'Kardiologi Pediatrik 1', 'alias' => 'Pediatrik1', 'stase_order' => 7, 'is_mandatory' => 1, 'color' => '#772687', 'font_color' => '#f5f5f5'],
            ['id' => 8, 'name' => 'Kardiovaskular Intensif 1 (ICCU)', 'alias' => 'ICCU', 'stase_order' => 8, 'is_mandatory' => 1, 'color' => '#89f5f4', 'font_color' => '#0a0000'],
            ['id' => 9, 'name' => 'Vaskular', 'alias' => 'Vaskular', 'stase_order' => 9, 'is_mandatory' => 1, 'color' => '#38842e', 'font_color' => '#f2f2f2'],
            ['id' => 10, 'name' => 'Kardiologi Nuklir dan Pencitraan', 'alias' => 'MSCT', 'stase_order' => 10, 'is_mandatory' => 1, 'color' => '#392ed1', 'font_color' => '#f0f0f0'],
            ['id' => 11, 'name' => 'Aritmia', 'alias' => 'Aritmia', 'stase_order' => 11, 'is_mandatory' => 1, 'color' => '#720ec4', 'font_color' => '#faf9f9'],
            ['id' => 12, 'name' => 'Kardiologi Prevensi dan Rehabilitasi', 'alias' => 'Rehab', 'stase_order' => 12, 'is_mandatory' => 1, 'color' => '#ff0fa7', 'font_color' => '#f4ecec'],
            ['id' => 13, 'name' => 'Kegawatan Kardiovaskular (UGD)', 'alias' => 'UGD', 'stase_order' => 13, 'is_mandatory' => 1, 'color' => '#ede502', 'font_color' => '#220202'],
            ['id' => 14, 'name' => 'ICU Umum dan Pasca Bedah', 'alias' => 'ICU', 'stase_order' => 14, 'is_mandatory' => 1, 'color' => '#18e74c', 'font_color' => null],
            ['id' => 15, 'name' => 'Bedah Kardio Vaskular', 'alias' => 'BEDAH', 'stase_order' => 15, 'is_mandatory' => 1, 'color' => '#ffb347', 'font_color' => null],
            ['id' => 16, 'name' => 'Kehamilan dan Penyakit Kardiovaskular', 'alias' => 'OBSGYN', 'stase_order' => 16, 'is_mandatory' => 1, 'color' => '#f292dd', 'font_color' => null],
            ['id' => 17, 'name' => 'Kardiologi Pediatrik 2', 'alias' => 'Pediatrik2', 'stase_order' => 17, 'is_mandatory' => 1, 'color' => '#aba0a0', 'font_color' => '#0d0c0c'],
            ['id' => 18, 'name' => 'Kardiovaskular Intensif 2 (ICCU)', 'alias' => 'ICCU-2', 'stase_order' => 18, 'is_mandatory' => 1, 'color' => '#00faf6', 'font_color' => '#141414'],
            ['id' => 19, 'name' => 'Ekhokardiografi 2', 'alias' => 'Ekho- 2', 'stase_order' => 19, 'is_mandatory' => 1, 'color' => '#dfe49b', 'font_color' => null],
            ['id' => 20, 'name' => 'Poliklinik', 'alias' => 'POLI', 'stase_order' => 20, 'is_mandatory' => 1, 'color' => '#140000', 'font_color' => '#f8f2f2'],
            ['id' => 21, 'name' => 'RSUD Banyumas', 'alias' => 'Banyumas', 'stase_order' => 21, 'is_mandatory' => 0, 'color' => '#6b4338', 'font_color' => null],
            ['id' => 22, 'name' => 'RSPAU Dr. S. Hardjolukito', 'alias' => '3RSHL', 'stase_order' => 22, 'is_mandatory' => 0, 'color' => '#ffdd00', 'font_color' => '#000000'],
            ['id' => 23, 'name' => 'RSUP Dr. Soeradji Tirtonegoro Klaten', 'alias' => 'Klaten', 'stase_order' => 23, 'is_mandatory' => 0, 'color' => '#40b828', 'font_color' => '#f8f7f7'],
            ['id' => 24, 'name' => 'Tesis', 'alias' => '3TS', 'stase_order' => 24, 'is_mandatory' => 1, 'color' => '#8e0101', 'font_color' => '#ffffff'],
            ['id' => 25, 'name' => 'Kardiologi Klinik Dewasa 2', 'alias' => 'KKV 2', 'stase_order' => 17, 'is_mandatory' => 1, 'color' => '#100eb4', 'font_color' => '#fdf7f7'],
            ['id' => 26, 'name' => 'Orientasi, Pembekalan, dan Diskusi Kasus', 'alias' => '1OPDK', 'stase_order' => 1, 'is_mandatory' => 1, 'color' => '#fdc4c4', 'font_color' => '#000000'],
            ['id' => 27, 'name' => 'Referat Basic', 'alias' => null, 'stase_order' => null, 'is_mandatory' => null, 'color' => null, 'font_color' => null],
            ['id' => 28, 'name' => 'Ujian Kompetensi', 'alias' => 'UK', 'stase_order' => null, 'is_mandatory' => null, 'color' => '#ffffff', 'font_color' => null],
            ['id' => 29, 'name' => 'Referat Clinic', 'alias' => null, 'stase_order' => null, 'is_mandatory' => null, 'color' => null, 'font_color' => null],
            ['id' => 30, 'name' => 'Referat Thesis', 'alias' => null, 'stase_order' => null, 'is_mandatory' => null, 'color' => null, 'font_color' => null],
            ['id' => 31, 'name' => 'Referal Penugasan Khusus', 'alias' => 'Referal', 'stase_order' => null, 'is_mandatory' => null, 'color' => '#4b1616', 'font_color' => null],
            ['id' => 32, 'name' => 'Orientasi dan Matrikulasi', 'alias' => 'orientasi', 'stase_order' => null, 'is_mandatory' => null, 'color' => '#a6a0a0', 'font_color' => '#ebeaea'],
            ['id' => 33, 'name' => 'RS Akademik UGM', 'alias' => 'RSA', 'stase_order' => null, 'is_mandatory' => null, 'color' => '#39ac5c', 'font_color' => '#fcfcfc'],
            ['id' => 34, 'name' => 'RS Referal Lain', 'alias' => 'RSRL', 'stase_order' => null, 'is_mandatory' => null, 'color' => '#1d1b1b', 'font_color' => '#f5f5f5'],
            ['id' => 35, 'name' => 'RS Referal Lain', 'alias' => 'RSRL', 'stase_order' => null, 'is_mandatory' => null, 'color' => '#1d1b1b', 'font_color' => '#f5f5f5'],
            ['id' => 36, 'name' => 'RS Referal Lain', 'alias' => 'RSRL', 'stase_order' => null, 'is_mandatory' => null, 'color' => '#1d1b1b', 'font_color' => '#f5f5f5'],
            ['id' => 37, 'name' => 'Ujian Kompetensi Kapten', 'alias' => 'Kapten', 'stase_order' => null, 'is_mandatory' => null, 'color' => '#f1f50f', 'font_color' => null],
            ['id' => 38, 'name' => 'Referat Clinic Divisi 2', 'alias' => 'RefClin 2', 'stase_order' => null, 'is_mandatory' => null, 'color' => '#130606', 'font_color' => '#e3e718'],
        ];

        foreach ($stases as $stase) {
            $stase['created_at'] = now();
            $stase['updated_at'] = now();
            DB::table('stases')->updateOrInsert(['id' => $stase['id']], $stase);
        }
    }
}
