<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDeckColumnInCardUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $userCards = app('db')->table('card_user')->get();

        Schema::table('card_user', function (Blueprint $table) {
            $table->dropColumn('deck');
        });

        Schema::table('card_user', function (Blueprint $table) {
            $table->unsignedTinyInteger('deck_id')->default(1)->after('level');
        });

        foreach ($userCards as $userCard) {

            switch ($userCard->level) {
                case 1:
                    $deckID = 1;
                    break;
                case 5:
                    $deckID = 12;
                    break;
                default:
                    $deckID = $userCard->deck[0] + 2;
                    break;
            }

            app('db')->table('card_user')
              ->where('user_id', $userCard->user_id)
              ->where('card_id', $userCard->card_id)
              ->update(['deck_id' => $deckID]);
        }
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
