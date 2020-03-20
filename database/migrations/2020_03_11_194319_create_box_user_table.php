<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoxUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('box_user', function (Blueprint $table) {
            $table->unsignedInteger('box_id');
            $table->unsignedInteger('user_id');
            $table->unsignedTinyInteger('session')->default(0);
            $table->dateTime('session_started_at')->nullable();

            $table->primary(['box_id', 'user_id']);

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
        Schema::dropIfExists('box_user');
    }
}
