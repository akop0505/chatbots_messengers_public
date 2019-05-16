<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leads_status_dict', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('leads', function (Blueprint $table) {
            $table->increments('id');
            $table->string('last_name',255)->nullable();
            $table->string('first_name',255);
            $table->string('father_name',255)->nullable();
            $table->string('phone',255)->nullable();
            $table->string('email',255)->nullable();
            $table->integer('owner_id',false,true)->nullable();
            $table->foreign('owner_id')->references('id')->on('users');
            $table->integer('project_id',false,true)->nullable();
            $table->foreign('project_id')->references('id')->on('projects');
            $table->integer('status',false,true)->nullable();
            $table->foreign('status')->references('id')->on('leads_status_dict');
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
        Schema::dropIfExists('leads_status_dict');
        Schema::dropIfExists('leads');
    }
}
