<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectOfficePhonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_office_phones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('phone','255');
            $table->string('extra','255')->nullable();
            $table->integer('project_id',false,true);
            $table->foreign('project_id')->references('id')->on('projects');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_office_phones');
    }
}
