<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePresencesTable extends Migration
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
CREATE TABLE `presences` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int DEFAULT NULL,
  `checkin` timestamp NULL DEFAULT NULL,
  `checkin_photo` varchar(100) DEFAULT NULL,
  `checkin_data` varchar(400) DEFAULT NULL COMMENT 'lat, lng, accuracy, distance, device',
  `checkin_note` varchar(250) DEFAULT NULL,
  `checkout` timestamp NULL DEFAULT NULL,
  `checkout_photo` varchar(100) DEFAULT NULL,
  `checkout_data` varchar(400) DEFAULT NULL,
  `checkout_note` varchar(200) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
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
        Schema::dropIfExists('presences');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
