<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGetwayTokenProjectMessengersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_messengers', function (Blueprint $table) {
            $table->renameColumn('token','bot_token');
            $table->text('gateway_token')->after('gateway_id')->nullable();
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
            $table->renameColumn('bot_token','token');
            $table->dropColumn('gateway_token');
        });
    }
}
