<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditAnswerableMessagesAddParametersField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('answerable_messages', function (Blueprint $table) {
            $table->text('template_params')->after('template_id')->nullable();
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
            $table->dropColumn('template_params');
        });
    }
}
