<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditAnswerableMessagesAddBotUsernameTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('answerable_messages', function (Blueprint $table) {
            $table->string('bot_username')->nullable()->after('channel');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('answerable_messages', function (Blueprint $table) {
            $table->dropColumn('bot_username');
        });
    }
}
