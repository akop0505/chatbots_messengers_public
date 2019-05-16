<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePresentRepliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('present_replies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('point');
            $table->string('text');
            $table->integer('menu_id',false,true);
            $table->timestamps();

            $table->foreign('menu_id')->references('id')->on('menus');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('present_replies');
    }
}
