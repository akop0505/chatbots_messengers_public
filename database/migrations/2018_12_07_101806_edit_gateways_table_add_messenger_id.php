<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditGatewaysTableAddMessengerId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gateways', function (Blueprint $table) {
            $table->integer('messenger_id',false,true)->nullable();
            $table->foreign('messenger_id')->references('id')->on('messengers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gateways', function (Blueprint $table) {
            $table->dropForeign('gateways_messenger_id_foreign');
            $table->dropIndex('gateways_messenger_id_foreign');
            $table->dropColumn('messenger_id');
        });
    }
}
