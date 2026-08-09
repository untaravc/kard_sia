<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LecturesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create('id_ID');

        $rows = [];
        for ($i = 0; $i < 10; $i++) {
            $rows[] = [
                'name' => 'dr. ' . $faker->unique()->name . ', Sp.JP',
                'name_alt' => null,
                'number' => $faker->unique()->numerify('198#####  ######  # ###'),
                'is_in_house' => $faker->boolean(70),
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password'),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('lectures')->insert($rows);
    }
}
