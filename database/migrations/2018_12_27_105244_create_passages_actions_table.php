<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePassagesActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('passages_actions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('passage_id',false,true);
            $table->foreign('passage_id')->references('id')->on('passages');
            $table->integer('lead_id',false,true);
            $table->foreign('lead_id')->references('id')->on('leads');
            $table->integer('project_id',false,true);
            $table->foreign('project_id')->references('id')->on('projects');
            $table->string('type',255);
            $table->text('options')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->enum('status',['pending','in_progress','complete','fail'])->default('pending');
            $table->string('error',255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('passages_actions');
    }
}
