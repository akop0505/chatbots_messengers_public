<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CretaeProjectKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_keys', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id',false,true);
            $table->foreign('project_id')->references('id')->on('projects');
            $table->text('access_key');
            $table->boolean('inactive')->default(false);
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
        Schema::dropIfExists('project_keys');
    }
}
