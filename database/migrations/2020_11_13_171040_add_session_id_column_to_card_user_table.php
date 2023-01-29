<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddSessionIdColumnToCardUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('card_user', function (Blueprint $table) {
            $table->unsignedInteger('session_id')->after('user_id')->nullable();

            $table->foreign('session_id')->references('id')->on('sessions')->onDelete('cascade');
        });

        $cards = \App\Models\Card::all();

        $sessions = \App\Models\Session::all();
        
        foreach (DB::table('card_user')->get() as $item) {
            $card = $cards->find($item->card_id);

            $session = $sessions->where('user_id', $item->user_id)->where('box_id', $card->box_id)->first();

            DB::table('card_user')->where([
                ['user_id', $item->user_id],
                ['card_id', $item->card_id]
            ])->update([
                'session_id' => $session->id
            ]);
        }

        Schema::table('card_user', function (Blueprint $table) {
            $table->unsignedInteger('session_id')->change();
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
