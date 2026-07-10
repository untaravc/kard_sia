<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateActivitiesTable extends Migration
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
CREATE TABLE `activities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `place` varchar(255) DEFAULT NULL,
  `speaker` varchar(200) DEFAULT NULL,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `note` text,
  `desc` text,
  `created_by` int DEFAULT NULL,
  `status` enum('draft','active','finish') DEFAULT 'draft',
  `lecture_pembimbing` varchar(100) DEFAULT NULL,
  `lecture_penguji` varchar(100) DEFAULT NULL,
  `lecture_pengampu` varchar(100) DEFAULT NULL,
  `link` varchar(250) DEFAULT NULL,
  `passcode` varchar(10) DEFAULT NULL,
  `category` tinyint DEFAULT NULL,
  `type` tinyint DEFAULT NULL,
  `activity_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `stase_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activities_start_date_index` (`start_date`)
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
        Schema::dropIfExists('activities');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
