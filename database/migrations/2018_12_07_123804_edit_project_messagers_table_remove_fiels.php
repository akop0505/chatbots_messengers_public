<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditProjectMessagersTableRemoveFiels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_messengers', function (Blueprint $table) {
            $table->dropForeign('project_messengers_messenger_id_foreign');
            $table->dropIndex('project_messengers_messenger_id_foreign');

            $table->dropColumn('messenger_id');
            $table->dropColumn('bot_username');
            $table->dropColumn('bot_name');
            $table->dropColumn('phone');
            $table->dropColumn('gateway_token');
            $table->dropColumn('bot_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_messengers', function (Blueprint $table) {
            //
        });
    }
}
