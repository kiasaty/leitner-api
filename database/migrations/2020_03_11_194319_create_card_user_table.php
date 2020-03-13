<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('card_user', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('card_id');
            $table->unsignedTinyInteger('level')->default(1);
            $table->unsignedSmallInteger('deck_id')->nullable();
            $table->timestamps();

            $table->primary(['card_id', 'user_id']);

            $table->foreign('card_id')->references('id')->on('cards')->onDelete('cascade');
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
        Schema::dropIfExists('card_user');
    }
}
