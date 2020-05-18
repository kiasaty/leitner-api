<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Session;

class AddEndedAtColumnInSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->timestamp('ended_at')->after('started_at')->nullable();
        });

        $sessions = Session::all();

        foreach ($sessions as $session) {
            $isSessionEnded = isset($session->started_at) && is_null($session->getNextCard());

            if ($isSessionEnded) {
                $endedAt = $session->user->cards()
                    ->where('box_id', $session->box_id)
                    ->latest('reviewed_at')
                    ->pluck('reviewed_at')
                    ->first();
                
                $session->ended_at = $endedAt;
                $session->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropColumn('ended_at');
        });
    }
}
