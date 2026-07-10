<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create('id_ID');
        $years = ['2023-01', '2023-07', '2024-01', '2024-07', '2025-01', '2025-07'];
        $statuses = ['active', 'active', 'active', 'nonactive', 'graduate'];

        $rows = [];
        for ($i = 0; $i < 20; $i++) {
            $rows[] = [
                'name' => 'dr. ' . $faker->unique()->name,
                'email' => $faker->unique()->safeEmail,
                'year' => $faker->randomElement($years),
                'password' => Hash::make('password'),
                'status' => $faker->randomElement($statuses),
                'link_token' => \Illuminate\Support\Str::random(20),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('students')->insert($rows);
    }
}
