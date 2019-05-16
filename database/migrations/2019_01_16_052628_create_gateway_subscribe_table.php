<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGatewaySubscribeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gateway_subscribes', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('action', ['template', 'code','phone']);
            $table->string('callback', 500)->nullable();

            $table->integer('template_id',false,true)->nullable();
            $table->foreign('template_id')->references('id')->on('templates');

            $table->integer('project_messenger_id',false,true);
            $table->foreign('project_messenger_id')->references('id')->on('project_messengers');

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
        Schema::dropIfExists('gateway_subscribes');
    }
}
