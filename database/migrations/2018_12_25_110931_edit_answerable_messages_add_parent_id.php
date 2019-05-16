<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditAnswerableMessagesAddParentId extends Migration
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
            $table->integer('parent_id',false,true)->nullable()->after('id');
            $table->enum('state',['waiting','answered','bot_simple','user_simple','user_answer'])->after('template_params')->nullable();
            $table->text('answer')->after('state');
            $table->foreign('parent_id')->references('id')->on('answerable_messages');
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
            $table->dropForeign('answerable_messages_parent_id_foreign');
            $table->dropIndex('answerable_messages_parent_id_foreign');
            $table->dropColumn('parent_id');
        });
    }
}
