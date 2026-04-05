<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConvertLettersTableToUtf8mb4 extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('letters')) {
            return;
        }

        DB::statement('ALTER TABLE `letters` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }

    public function down()
    {
        // Intentionally left blank: converting back can corrupt data.
    }
}

