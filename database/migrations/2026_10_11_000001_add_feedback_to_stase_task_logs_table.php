<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFeedbackToStaseTaskLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stase_task_logs', function (Blueprint $table) {
            $table->unsignedTinyInteger('feedback_rating')->nullable()->after('conclusion');
            $table->text('feedback_text')->nullable()->after('feedback_rating');
            $table->timestamp('feedback_at')->nullable()->after('feedback_text');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stase_task_logs', function (Blueprint $table) {
            $table->dropColumn(['feedback_rating', 'feedback_text', 'feedback_at']);
        });
    }
}
