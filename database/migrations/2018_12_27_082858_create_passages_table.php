<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePassagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('passages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sequence_id',false,true);
            $table->foreign('sequence_id')->references('id')->on('sequences');
            $table->integer('lead_id',false,true);
            $table->foreign('lead_id')->references('id')->on('leads');
            $table->timestamps();
            $table->softDeletes('closed_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('passages');
    }
}
