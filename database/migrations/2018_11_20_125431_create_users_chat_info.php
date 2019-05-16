<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersChatInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_chat_info', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id',false,true)->nullable(true);
            $table->integer('chat_id',false,true)->nullable(true);
            $table->integer('bot_id',false,true)->nullable(true);
            $table->text('user')->nullable(true);
            $table->text('bot')->nullable(true);
            $table->enum('channel',['telegram','viber','whatsapp','vk','fb'])->nullable(true);
            $table->foreign('project_id')->references('id')->on('projects');
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
        Schema::dropIfExists('users_chat_info');
    }
}
