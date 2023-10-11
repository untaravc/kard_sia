<?php

namespace App\Http\Traits;

trait AgendaTrait
{
    public $categories = [
        [
            'id'   => 1,
            'name' => 'Laporan Jaga',
            'desc' => '',
            'slug' => 'laporan-jaga',
        ],
        [
            'id'   => 2,
            'name' => 'Konfrensi Bedah',
            'desc' => '',
            'slug' => 'konfrensi-bedah',
        ],
        [
            'id'   => 3,
            'name' => 'Ilmiah Divisi',
            'desc' => '',
            'slug' => 'ilmiah-divisi',
        ],
        [
            'id'   => 4,
            'name' => 'Club',
            'desc' => 'jurnal, Hemodinamik, Aritmia, Rehab, Echo, dll',
            'slug' => '',
        ],
        [
            'id'   => 5,
            'name' => 'Tesis',
            'desc' => 'Ujian Tesis, Seminar Hasil, Outline, dll',
            'slug' => 'tesis',
        ],
        [
            'id'   => 6,
            'name' => 'Stase',
            'desc' => '(Book Reading, Jurnal Reading, CBD, Laporan kasus, Referat, dll)',
            'slug' => 'stase',
        ],
        [
            'id'   => 7,
            'name' => 'Lain lain',
            'desc' => 'Rapat Staf, Kuliah Tamu, dll',
            'slug' => 'lain-lain',
        ],
    ];
}
