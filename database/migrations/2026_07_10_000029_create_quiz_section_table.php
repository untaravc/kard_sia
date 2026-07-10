<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateQuizSectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::unprepared(<<<'SQL'
CREATE TABLE `quiz_section` (
  `id` int DEFAULT NULL,
  `section_id` int DEFAULT NULL,
  `quiz_id` int DEFAULT NULL,
  `order` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1
SQL
        );
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::dropIfExists('quiz_section');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
