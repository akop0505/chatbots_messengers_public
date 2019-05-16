<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('country')->nullable();
            $table->string('name')->nullable();
            $table->integer('event_id',false,true);
            $table->integer('receiver_id',false,true);
            $table->string('payment_type')->nullable();
            $table->string('parameter_id');
            $table->text('text')->nullable();
            $table->integer('menu_id',false,true)->nullable();
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('events');
            $table->foreign('receiver_id')->references('id')->on('receivers');
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
        Schema::dropIfExists('templates');
    }
}
