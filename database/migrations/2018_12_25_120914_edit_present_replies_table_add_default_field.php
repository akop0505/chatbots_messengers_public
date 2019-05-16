<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditPresentRepliesTableAddDefaultField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('present_replies', function (Blueprint $table) {
            $table->boolean('default')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('present_replies', function (Blueprint $table) {
            $table->dropColumn('default');
        });
    }
}
