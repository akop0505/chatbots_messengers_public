<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditReceiversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('receivers', function (Blueprint $table) {
            $table->softDeletes();
            $table->integer('project_id',false,true);
            $table->foreign('project_id')->references('id')->on('projects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('receivers', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
            $table->dropForeign('receivers_project_id_foreign');
            $table->dropIndex('receivers_project_id_foreign');
            $table->dropColumn('project_id');
        });
    }
}
