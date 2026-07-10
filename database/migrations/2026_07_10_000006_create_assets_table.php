<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAssetsTable extends Migration
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
CREATE TABLE `assets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `number` varchar(20) DEFAULT NULL,
  `owner` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `price` int DEFAULT NULL,
  `estimate_month` int DEFAULT NULL,
  `supplier` varchar(100) DEFAULT NULL,
  `photo_url` varchar(300) DEFAULT NULL,
  `photo_urls` text,
  `brand` varchar(100) DEFAULT NULL,
  `purchase_date` datetime DEFAULT NULL,
  `category` varchar(20) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `description` text,
  `warranty_until` datetime DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
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
        Schema::dropIfExists('assets');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
