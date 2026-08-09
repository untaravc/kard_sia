<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(StudentsTableSeeder::class);
        $this->call(LecturesTableSeeder::class);
        $this->call(StasesTableSeeder::class);
        $this->call(SettingsTableSeeder::class);
        $this->call(MenuTableSeeder::class);

        // Seeders suffixed "Tmp" are temporary/manual-only (e.g. one-off data
        // imports) and must not be called here. Run them explicitly with
        // `php artisan db:seed --class=SeederNameTmp`.
    }
}
