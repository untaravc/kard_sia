<?php

use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rows = [
            [
                'name' => 'Nama Aplikasi',
                'label' => 'app.name',
                'value' => 'BLU',
                'status' => 1,
            ],
            [
                'name' => 'Prefix Aplikasi',
                'label' => 'app.prefix',
                'value' => 'KardiologiFkkmk',
                'status' => 1,
            ],
            [
                'name' => 'Logo URL',
                'label' => 'app.logo',
                'value' => '',
                'status' => 1,
            ],
            [
                'name' => 'BackgroundImageUrl',
                'label' => 'app.bg-img-url',
                'value' => '',
                'status' => 1,
            ],
            [
                'name' => 'Login Description',
                'label' => 'app.login-desc',
                'value' => "Code Blue is a call to act without delay.\nBLU prepares cardiology residents for decisive moments.\nBecause every heartbeat matters.",
                'status' => 1,
            ],
            [
                'name' => 'Nama Departemen',
                'label' => 'app.department-name',
                'value' => '',
                'status' => 1,
            ],
            [
                'name' => 'Email Pengembang',
                'label' => 'app.dev-email',
                'value' => '',
                'status' => 1,
            ],
            [
                'name' => 'Phone Pengembang',
                'label' => 'app.dev-phone',
                'value' => '',
                'status' => 1,
            ],
            [
                'name' => 'Kontak Email',
                'label' => 'app.contact-email',
                'value' => '',
                'status' => 1,
            ],
            [
                'name' => 'Kontak Phone',
                'label' => 'app.contact-phone',
                'value' => '',
                'status' => 1,
            ],
            [
                'name' => 'Kontak Alamat',
                'label' => 'app.contact-address',
                'value' => '',
                'status' => 1,
            ],
            [
                'name' => 'Nama Universitas',
                'label' => 'app.university-name',
                'value' => '',
                'status' => 1,
            ],
            [
                'name' => 'Nama Fakultas',
                'label' => 'app.faculty-name',
                'value' => '',
                'status' => 1,
            ],
            [
                'name' => 'Versi Logbook',
                'label' => 'app.version-logbook',
                'value' => '',
                'status' => 1,
            ],
            [
                'name' => 'Versi Scoring List',
                'label' => 'app.version-scoring-list',
                'value' => '1',
                'status' => 1,
            ],
        ];

        foreach ($rows as $row) {
            DB::table('settings')->updateOrInsert(
                ['label' => $row['label']],
                [
                    'name' => $row['name'],
                    'value' => $row['value'],
                    'status' => $row['status'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
