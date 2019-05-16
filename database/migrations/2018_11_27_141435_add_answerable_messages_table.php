<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAnswerableMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('answerable_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('message_id',false,true);
            $table->integer('template_id',false,true)->nullable();
            $table->integer('chat_id',false,true)->nullable();
            $table->integer('chat_user_id',false,true)->nullable();
            $table->enum('state',['waiting','answered'])->nullable();
            $table->string('answer')->nullable();
            $table->enum('channel',['telegram','viber','whatsapp','vk','sms','email','fb'])->nullable();
            $table->timestamps();

            $table->foreign('template_id')->references('id')->on('templates');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('answerable_messages');
    }
}
