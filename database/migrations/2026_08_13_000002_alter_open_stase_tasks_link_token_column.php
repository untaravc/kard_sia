<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterOpenStaseTasksLinkTokenColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE `open_stase_tasks` MODIFY `link_token` VARCHAR(100) DEFAULT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE `open_stase_tasks` MODIFY `link_token` VARCHAR(30) DEFAULT NULL');
    }
}
