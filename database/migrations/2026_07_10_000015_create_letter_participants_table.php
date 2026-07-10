<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateLetterParticipantsTable extends Migration
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
CREATE TABLE `letter_participants` (
  `id` int NOT NULL AUTO_INCREMENT,
  `letter_id` int DEFAULT NULL,
  `auth_type` enum('user','student','lecture') DEFAULT NULL,
  `auth_id` int DEFAULT NULL,
  `auth_name` varchar(200) DEFAULT NULL,
  `auth_number` varchar(30) DEFAULT NULL,
  `type` varchar(200) DEFAULT NULL,
  `label` varchar(200) DEFAULT NULL,
  `status` tinyint DEFAULT NULL,
  `token` varchar(200) DEFAULT NULL,
  `validated_at` timestamp NULL DEFAULT NULL,
  `notify_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
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
        Schema::dropIfExists('letter_participants');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
