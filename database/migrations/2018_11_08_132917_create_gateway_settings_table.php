<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGatewaySettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gateway_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_messenger_id',false,true);
            $table->string('field_name');
            $table->string('field_value');
            $table->timestamps();
            $table->foreign('project_messenger_id')->references('id')->on('project_messengers');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gateway_settings');
    }
}
