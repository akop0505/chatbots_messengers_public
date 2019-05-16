<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->integer('gateway_id',false,true);

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
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign('projects_gateway_id_foreign');
            $table->dropIndex('projects_gateway_id_foreign');
            $table->dropColumn('gateway_id');
        });
    }
}
