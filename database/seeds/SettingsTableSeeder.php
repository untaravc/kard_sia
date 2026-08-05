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
                'name' => 'Login Description',
                'label' => 'app.login-desc',
                'value' => "Code Blue is a call to act without delay.\nBLU prepares cardiology residents for decisive moments.\nBecause every heartbeat matters.",
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
