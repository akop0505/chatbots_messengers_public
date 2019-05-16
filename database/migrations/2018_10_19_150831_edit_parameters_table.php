<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parameters', function (Blueprint $table) {
            $table->softDeletes();
            $table->boolean('inactive')->default(0)->nullable();
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
        Schema::table('parameters', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
            $table->dropColumn('inactive');
            $table->dropForeign('parameters_project_id_foreign');
            $table->dropIndex('parameters_project_id_foreign');
            $table->dropColumn('project_id');
        });
    }
}
