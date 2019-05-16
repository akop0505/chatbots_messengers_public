<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditMenuItemsAddReplayParamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->boolean('auto_reply')->default(0);
            $table->enum('reply_type',['present','template','none'])->default('none');
            $table->integer('template_id',false,true)->nullable()->default(null);
            $table->integer('present_reply_id',false,true)->nullable()->default(null);

            $table->foreign('template_id')->references('id')->on('templates');
            $table->foreign('present_reply_id')->references('id')->on('present_replies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn('auto_reply');
            $table->dropColumn('reply_type');
            $table->dropColumn('template_id');
            $table->dropColumn('present_id');
        });
    }
}
