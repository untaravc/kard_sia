<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateFormAnswersTable extends Migration
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
CREATE TABLE `form_answers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `form_response_id` int NOT NULL,
  `form_field_id` int NOT NULL,
  `value` mediumtext,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `form_answers_form_response_id_index` (`form_response_id`),
  KEY `form_answers_form_field_id_index` (`form_field_id`)
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
        Schema::dropIfExists('form_answers');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
