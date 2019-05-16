<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGatewayIdToprojectMessengersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_messengers', function (Blueprint $table) {
            $table->text('token')->nullable()->after('phone');
            $table->integer('gateway_id',false,true)->nullable()->after('phone');
            $table->foreign('gateway_id')->references('id')->on('gateways');
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
            $table->dropForeign('project_messengers_gateway_id_foreign');
            $table->dropIndex('project_messengers_gateway_id_foreign');
            $table->dropColumn('gateway_id');
        });
    }
}
