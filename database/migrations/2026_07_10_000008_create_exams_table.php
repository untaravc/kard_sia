<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateExamsTable extends Migration
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
CREATE TABLE `exams` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` tinytext,
  `desc` tinytext,
  `lecture_id` mediumint DEFAULT NULL,
  `stase_id` tinyint DEFAULT NULL,
  `stase_task_id` int DEFAULT NULL,
  `link` tinytext,
  `duration` mediumint DEFAULT NULL,
  `token` tinytext,
  `status` tinyint DEFAULT NULL,
  `available_at` timestamp NULL DEFAULT NULL,
  `expired_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1
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
        Schema::dropIfExists('exams');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
