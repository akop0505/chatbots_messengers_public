<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditAnswerableMessagesAssFiledsForFile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('answerable_messages', function (Blueprint $table) {
            $table->string("type")->after('state')->nullable(true)->default('text');
            $table->text("file_json")->nullable(true)->after('answer');
            $table->string("file_path")->nullable(true)->after('file_json');
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
            if(in_array('type',$table->getColumns())){
                $table->dropColumn('type');
            }
            if(in_array('file_json',$table->getColumns())){
                $table->dropColumn('file_json');
            }
            if(in_array('file_path',$table->getColumns())){
                $table->dropColumn('file_path');
            }
        });
    }
}
