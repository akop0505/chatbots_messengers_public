<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditProjectKeysAddUserId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_keys', function (Blueprint $table) {
            $table->integer('user_id',false,true)->nullable();
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_keys', function (Blueprint $table) {
            $table->dropForeign('project_keys_user_id_foreign');
            $table->dropIndex('project_keys_user_id_foreign');
            $table->dropColumn('user_id');
        });
    }
}
