<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectMessengersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_messengers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id',false,true);
            $table->integer('messenger_id',false,true);
            $table->string('bot_username')->nullable(true);
            $table->string('bot_name')->nullable(true);
            $table->string('phone')->nullable(true);
            $table->integer('permission_id',false,true)->nullable(true);
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects');
            $table->foreign('messenger_id')->references('id')->on('messengers');
            $table->foreign('permission_id')->references('id')->on('permissions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_messengers');
    }
}
