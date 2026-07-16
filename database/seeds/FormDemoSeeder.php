<?php

use App\Models\Form;
use Illuminate\Database\Seeder;

class FormDemoSeeder extends Seeder
{
    /**
     * Seed two demo forms so the builder / public fill flow can be tried
     * end to end. Run with: php artisan db:seed --class=FormDemoSeeder
     *
     * @return void
     */
    public function run()
    {
        // A public form that showcases every field type.
        $public = Form::create([
            'title'          => 'Survei Kepuasan Layanan',
            'slug'           => 'survei-kepuasan-layanan',
            'description'    => "Formulir contoh berisi semua tipe pertanyaan.\nSilakan diisi untuk mencoba.",
            'visibility'     => 'public',
            'status'         => 1,
            'allow_multiple' => 1,
            'collect_email'  => 1,
        ]);

        $fields = [
            ['type' => 'text', 'label' => 'Nama unit / instansi', 'is_required' => 1, 'placeholder' => 'Contoh: Poli Jantung'],
            ['type' => 'textarea', 'label' => 'Ceritakan pengalaman Anda', 'description' => 'Bebas dan sedetail mungkin'],
            ['type' => 'radio', 'label' => 'Bagaimana penilaian Anda?', 'is_required' => 1, 'options' => ['Sangat baik', 'Baik', 'Cukup', 'Kurang']],
            ['type' => 'checkbox', 'label' => 'Layanan yang Anda gunakan', 'options' => ['Pendaftaran', 'Konsultasi', 'Laboratorium', 'Farmasi']],
            ['type' => 'select', 'label' => 'Dari mana Anda tahu layanan ini?', 'options' => ['Teman', 'Media sosial', 'Website', 'Lainnya']],
            ['type' => 'number', 'label' => 'Berapa kali Anda berkunjung bulan ini?', 'placeholder' => '0'],
            ['type' => 'email', 'label' => 'Email untuk tindak lanjut', 'description' => 'Opsional'],
            ['type' => 'date', 'label' => 'Tanggal kunjungan terakhir'],
            ['type' => 'rating', 'label' => 'Beri bintang untuk pelayanan kami', 'is_required' => 1, 'max_rating' => 5],
        ];

        foreach ($fields as $i => $field) {
            $public->fields()->create(array_merge([
                'description' => null,
                'placeholder' => null,
                'options'     => null,
                'is_required' => 0,
                'max_rating'  => null,
                'position'    => $i,
            ], $field));
        }

        // A login-required form (fillable only by an authenticated respondent).
        $private = Form::create([
            'title'          => 'Evaluasi Internal (Login)',
            'slug'           => 'evaluasi-internal',
            'description'    => 'Formulir ini hanya dapat diisi setelah login.',
            'visibility'     => 'auth',
            'status'         => 1,
            'allow_multiple' => 0,
            'collect_email'  => 0,
        ]);

        $private->fields()->create([
            'type' => 'radio', 'label' => 'Apakah target tercapai?',
            'options' => ['Ya', 'Sebagian', 'Tidak'], 'is_required' => 1, 'position' => 0,
        ]);
        $private->fields()->create([
            'type' => 'textarea', 'label' => 'Catatan tambahan', 'position' => 1,
        ]);

        $this->command->info('Demo forms created: /form/survei-kepuasan-layanan and /form/evaluasi-internal');
    }
}
