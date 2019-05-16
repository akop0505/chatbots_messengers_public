<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditUsersChatInfoTableIntegersToString extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_chat_info', function (Blueprint $table) {
            $table->dropColumn('chat_id');
            $table->dropColumn('bot_id');

        });
        Schema::table('users_chat_info', function (Blueprint $table) {
            $table->text('chat_id')->nullable()->after('project_id');
            $table->text('bot_id')->nullable()->after('chat_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_chat_info', function (Blueprint $table) {
            //
        });
    }
}
