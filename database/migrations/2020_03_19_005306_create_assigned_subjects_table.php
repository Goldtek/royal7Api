<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssignedSubjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assigned_subjects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('classId');
            $table->bigInteger('userId');
            $table->bigInteger('subjectId');
            $table->bigInteger('sessionId');
            $table->bigInteger('school_id');
            $table->string('term');
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
        Schema::dropIfExists('assigned_courses');
    }
}
