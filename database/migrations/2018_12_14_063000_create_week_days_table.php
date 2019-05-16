<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeekDaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('week_days', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title','255');
            $table->integer('project_id',false,true);
            $table->foreign('project_id')->references('id')->on('projects');
            $table->string('from','255');
            $table->string('to','255');
            $table->integer('order');
            $table->boolean('enabled')->default(false);
            $table->softDeletes();
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
        Schema::dropIfExists('week_days');
    }
}
