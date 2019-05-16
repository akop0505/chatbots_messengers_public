<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSidebarNavHasRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sidebar_nav_has_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sidebar_nav_id', false,true);
            $table->integer('role_id', false,true);
            $table->foreign('sidebar_nav_id')->references('id')->on('sidebar_navs');
            $table->foreign('role_id')->references('id')->on('roles');
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
        Schema::dropIfExists('sidebar_nav_has_roles');
    }
}
