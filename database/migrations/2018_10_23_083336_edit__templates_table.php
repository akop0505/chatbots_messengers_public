<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('templates', function (Blueprint $table) {
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
        Schema::table('templates', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
            $table->dropForeign('templates_project_id_foreign');
            $table->dropIndex('templates_project_id_foreign');
            $table->dropColumn('project_id');
        });
    }
}
