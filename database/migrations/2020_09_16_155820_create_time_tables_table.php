<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimeTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('times_table', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps('start_time');
            $table->timestamps('end_time');
            $table->timestamps('date');
            $table->bigInteger('userId');
            $table->bigInteger('schoolId');
            $table->bigInteger('sessionId');
            $table->bigInteger('subjectId');
            $table->bigInteger('classId');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('time_tables');
    }
}
