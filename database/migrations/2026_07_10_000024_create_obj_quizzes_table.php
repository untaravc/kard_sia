<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateObjQuizzesTable extends Migration
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
CREATE TABLE `obj_quizzes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lecture_id` int DEFAULT NULL,
  `category_id` varchar(50) DEFAULT NULL,
  `book` varchar(255) DEFAULT NULL,
  `book_number` int DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `question_image` varchar(200) DEFAULT NULL,
  `question` mediumtext,
  `option_1` varchar(225) DEFAULT NULL,
  `option_2` varchar(225) DEFAULT NULL,
  `option_3` varchar(225) DEFAULT NULL,
  `option_4` varchar(225) DEFAULT NULL,
  `option_5` varchar(225) DEFAULT NULL,
  `option_6` varchar(225) DEFAULT NULL,
  `answer` varchar(20) DEFAULT NULL,
  `score` int DEFAULT NULL,
  `note` mediumtext,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint DEFAULT NULL,
  `has_img` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
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
        Schema::dropIfExists('obj_quizzes');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
