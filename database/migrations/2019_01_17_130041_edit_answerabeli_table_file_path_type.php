<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditAnswerabeliTableFilePathType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('answerable_messages', function (Blueprint $table) {
            $table->dropColumn("file_path");
        });
        Schema::table('answerable_messages', function (Blueprint $table) {
            $table->text("file_path")->nullable(true)->after('file_json');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('answerable_messages', function (Blueprint $table) {
            //
        });
    }
}
