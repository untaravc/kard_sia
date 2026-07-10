<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateStudentLogsTable extends Migration
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
CREATE TABLE `student_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int DEFAULT NULL,
  `lecture_id` int DEFAULT NULL,
  `type` mediumtext,
  `stase_id` mediumint DEFAULT NULL,
  `stase_log_id` mediumint DEFAULT NULL,
  `stase_task_id` mediumint DEFAULT NULL,
  `stase_task_log_id` mediumint DEFAULT NULL,
  `field_1` mediumtext,
  `field_2` mediumtext,
  `field_3` mediumtext,
  `field_4` mediumtext,
  `field_5` mediumtext,
  `field_6` mediumtext,
  `date` date DEFAULT NULL,
  `status` tinyint DEFAULT NULL,
  `photo` varchar(200) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lecture_id` (`lecture_id`)
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
        Schema::dropIfExists('student_logs');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
