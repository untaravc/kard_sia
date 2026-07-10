<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateLettersTable extends Migration
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
CREATE TABLE `letters` (
  `id` int NOT NULL AUTO_INCREMENT,
  `number` varchar(100) DEFAULT NULL,
  `auth_type` enum('user','student','lecture') DEFAULT NULL,
  `auth_id` int DEFAULT NULL,
  `auth_name` varchar(200) DEFAULT NULL,
  `auth_number` varchar(30) DEFAULT NULL,
  `type` varchar(200) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `label` varchar(200) DEFAULT NULL,
  `title` varchar(500) DEFAULT NULL,
  `subtitle` varchar(500) DEFAULT NULL,
  `intro` mediumtext,
  `body` mediumtext,
  `outro` mediumtext,
  `status` tinyint DEFAULT NULL,
  `token` varchar(200) DEFAULT NULL,
  `attachment_content` text,
  `attachment_label` varchar(200) DEFAULT NULL,
  `custom_invitation` text,
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
        Schema::dropIfExists('letters');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
