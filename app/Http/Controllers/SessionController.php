<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\CardResource;
use App\Usecases\SessionStarter;

class SessionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Start a new learning session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $boxID
     * @return void
     */
    public function create(Request $request, $boxID)
    {
        $user = $request->user();
        
        if ($user->hasSession($boxID)) {
            abort(422, 'There is already a session on this box.');
        }
        
        $user->createSession($boxID);
    }

    /**
     * Start a new learning session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $boxID
     * @param  \App\Usecases\SessionStarter  $sessionStarter
     * @return \Illuminate\Http\Response|void
     */
    public function start(Request $request, $boxID, SessionStarter $sessionStarter)
    {
        $session = $request->user()->getSession($boxID);

        $this->authorize('update', $session);

        $sessionStarter->start($session);

        if ($nextCard = $session->getNextCard()) {
            return new CardResource($nextCard);
        }
    }
}
