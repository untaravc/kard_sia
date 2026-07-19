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
            'app_name' => 'BLU',
            'app_motto' => 'Code Blue is a call to act without delay.',
            'app_color' => '#0ca5e9',
            'app_logo_url' => '',
        ];

        foreach ($rows as $label => $value) {
            DB::table('settings')->updateOrInsert(
                ['label' => $label],
                [
                    'value' => $value,
                    'status' => 1,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
