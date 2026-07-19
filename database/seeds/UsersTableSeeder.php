<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'Admin',
                'email' => 'admin@blu.test',
                'phone' => '081200000002',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->seedLectures();
        $this->seedStudents();
    }

    private function seedLectures()
    {
        $faker = \Faker\Factory::create('id_ID');

        // Fixed demo account (used by the login page's "Lecture" demo button).
        $rows = [
            [
                'name' => 'dr. Demo Lecture, Sp.JP',
                'name_alt' => null,
                'number' => $faker->unique()->numerify('198#####  ######  # ###'),
                'is_in_house' => true,
                'email' => 'lecture@blu.test',
                'password' => Hash::make('password'),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('lectures')->insert($rows);
    }

    private function seedStudents()
    {
        $faker = \Faker\Factory::create('id_ID');
        $years = ['2023-01', '2023-07', '2024-01', '2024-07', '2025-01', '2025-07'];
        $statuses = ['active', 'active', 'active', 'nonactive', 'graduate'];

        // Fixed demo account (used by the login page's "Student" demo button).
        $rows = [
            [
                'name' => 'dr. Demo Student',
                'email' => 'student@blu.test',
                'year' => '2025-07',
                'password' => Hash::make('password'),
                'status' => 'active',
                'link_token' => Str::random(20),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('students')->insert($rows);
    }
}
