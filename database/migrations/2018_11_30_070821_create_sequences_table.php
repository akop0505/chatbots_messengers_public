<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSequencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sequences', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',255);
            $table->string('api_alias',255);
            $table->integer('project_id',false, true);
            $table->boolean('by_default')->nullable();
            $table->boolean('for_nonworking_time')->nullable();
            $table->timestamps();

            $table->softDeletes();

            $table->foreign('project_id')->references('id')->on('projects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sequences');
    }
}
