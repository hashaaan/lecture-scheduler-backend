<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHallAllocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hall_allocations', function (Blueprint $table) {
            $table->id();
            $table->integer('hall_id');
            $table->integer('schedule_id');
            $table->string('note',400)->nullable();
            $table->integer('lecture_id')->nullable();
            $table->integer('student_count');
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
        Schema::dropIfExists('hall_allocations');
    }
}
