<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSectionSemesterSksDurationToStasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stases', function (Blueprint $table) {
            $table->string('section', 100)->nullable()->after('lecture_name');
            $table->string('semester', 20)->nullable()->after('section');
            $table->integer('sks')->nullable()->after('semester');
            $table->string('duration', 50)->nullable()->after('sks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stases', function (Blueprint $table) {
            $table->dropColumn(['section', 'semester', 'sks', 'duration']);
        });
    }
}
