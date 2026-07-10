<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAssetLogsTable extends Migration
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
CREATE TABLE `asset_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `auth_id` int DEFAULT NULL,
  `auth_type` varchar(20) DEFAULT NULL,
  `asset_id` int DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `note` varchar(200) DEFAULT NULL,
  `location` varchar(50) DEFAULT NULL,
  `photo_urls` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
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
        Schema::dropIfExists('asset_logs');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
