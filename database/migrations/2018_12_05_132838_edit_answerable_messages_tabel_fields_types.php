<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditAnswerableMessagesTabelFieldsTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('answerable_messages', function (Blueprint $table) {
            $table->dropColumn('message_id');
            $table->dropColumn('chat_id');
            $table->dropColumn('chat_user_id');
        });
        Schema::table('answerable_messages', function (Blueprint $table) {
            $table->string('message_id')->nullable();
            $table->string('chat_id')->nullable();
            $table->string('chat_user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('answerable_messges', function (Blueprint $table) {
            //
        });
    }
}
