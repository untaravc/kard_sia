<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLetterParticipantsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('letter_participants')) {
            return;
        }

        Schema::create('letter_participants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('letter_id')->index();
            $table->string('auth_type')->nullable()->index();
            $table->unsignedBigInteger('auth_id')->nullable()->index();
            $table->string('auth_name')->nullable();
            $table->string('type')->nullable()->index();
            $table->string('label')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->string('token')->nullable()->index();
            $table->timestamp('validated_at')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('letter_participants');
    }
}

