<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeAndColumnForLogicTemplateMessage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->integer('false_template_id',false,true)->after('template_id')->nullable();
            $table->foreign('false_template_id')->references('id')->on('templates');
        });
        DB::statement("ALTER TABLE menu_items MODIFY reply_type ENUM('present','template','logic_template','none') DEFAULT 'none' NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn('false_template_id');
            $table->dropForeign('false_template_id');
        });
        DB::statement("ALTER TABLE menu_items MODIFY reply_type ENUM('present','template','none') DEFAULT 'none' NOT NULL");
    }
}
