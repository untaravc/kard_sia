<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddValidationFieldsToOpenStaseTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(<<<'SQL'
ALTER TABLE `open_stase_tasks`
  ADD COLUMN `validated_at` timestamp NULL DEFAULT NULL,
  ADD COLUMN `validated_method` enum('qr','code','manual') DEFAULT NULL,
  ADD COLUMN `validated_lat` decimal(10,7) DEFAULT NULL,
  ADD COLUMN `validated_lng` decimal(10,7) DEFAULT NULL,
  ADD COLUMN `validated_by` int DEFAULT NULL
SQL
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement(<<<'SQL'
ALTER TABLE `open_stase_tasks`
  DROP COLUMN `validated_at`,
  DROP COLUMN `validated_method`,
  DROP COLUMN `validated_lat`,
  DROP COLUMN `validated_lng`,
  DROP COLUMN `validated_by`
SQL
        );
    }
}
