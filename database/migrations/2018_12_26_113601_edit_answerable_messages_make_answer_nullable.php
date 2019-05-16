<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditAnswerableMessagesMakeAnswerNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('answerable_messages', function (Blueprint $table) {
            $table->dropColumn('state');
            $table->dropColumn('answer');
        });

        Schema::table('answerable_messages', function (Blueprint $table) {
            $table->enum('state',['waiting','answered','bot_simple','user_simple','user_answer'])->after('template_params')->nullable();
            $table->text('answer')->after('state')->nullable();
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
