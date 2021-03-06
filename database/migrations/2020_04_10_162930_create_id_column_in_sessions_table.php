<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIdColumnInSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropForeign('box_user_user_id_foreign');
            $table->dropForeign('box_user_box_id_foreign');
            $table->dropPrimary();
        });
        
        Schema::table('sessions', function (Blueprint $table) {
            $table->increments('id')->first();
            $table->timestamps();
            $table->unique(['box_id','user_id']);
            $table->foreign('box_id')->references('id')->on('boxes')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
