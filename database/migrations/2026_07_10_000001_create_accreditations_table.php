<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAccreditationsTable extends Migration
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
CREATE TABLE `accreditations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `parent_idx` varchar(20) DEFAULT NULL,
  `idx` varchar(20) DEFAULT NULL,
  `title` text,
  `description` text,
  `main_element` text,
  `main_element_fulfilment` text,
  `content` text,
  `is_complete` tinyint(1) DEFAULT '0',
  `attachment_urls` json DEFAULT NULL,
  `student_ids` json DEFAULT NULL,
  `lecture_ids` json DEFAULT NULL,
  `user_ids` json DEFAULT NULL,
  `auth_type` varchar(50) DEFAULT NULL,
  `auth_id` varchar(100) DEFAULT NULL,
  `auth_name` varchar(100) DEFAULT NULL,
  `sample` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
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
        Schema::dropIfExists('accreditations');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
