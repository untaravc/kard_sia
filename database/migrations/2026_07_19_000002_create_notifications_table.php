<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
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
CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `auth_type` varchar(10) DEFAULT NULL,
  `auth_id` int DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `content` text,
  `link` text,
  `is_read` tinyint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_auth_type_auth_id_index` (`auth_type`,`auth_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
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
        Schema::dropIfExists('notifications');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
