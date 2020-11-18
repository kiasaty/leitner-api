<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameCardUserTableAndDropUserIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('card_user', function (Blueprint $table) {
            $table->dropForeign('card_user_card_id_foreign');
            $table->dropForeign('card_user_user_id_foreign');
            $table->dropPrimary();
            
            $table->dropColumn('user_id');
            
            $table->dropForeign('card_user_session_id_foreign');
        });

        Schema::rename('card_user', 'session_card');

        Schema::table('session_card', function (Blueprint $table) {
            $table->foreign('session_id')->references('id')->on('sessions')->onDelete('cascade');
            $table->foreign('card_id')->references('id')->on('cards')->onDelete('cascade');
            
            $table->primary(['session_id', 'card_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
