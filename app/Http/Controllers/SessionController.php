<?php

namespace App\Http\Controllers;

use App\Box;
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

    /**
     * Get the next card.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $boxID
     * @return \Illuminate\Http\Response
     */
    public function next(Request $request, $boxID)
    {
        $session = $request->user()->getSession($boxID);

        $this->authorize('update', $session);

        if (! $session->isRunning()) {
            abort(422, 'There is no acive session!');
        }

        if ($session->isCompleted()) {
            abort(422, 'The current session is completed!');
        }

        $card = $session->getNextCard();
        
        return new CardResource($card);
    }

    /**
     * Review the given card.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $boxID
     * @param  int  $cardID
     * @return \Illuminate\Http\Response
     */
    public function review(Request $request, $boxID, $cardID)
    {
        $validatedInput = $this->validate($request, [
            'remember'  => 'required|boolean'
        ]);

        $session = $request->user()->getSession($boxID);

        $this->authorize('update', $session);

        $card = $session->findCardOrFail($cardID);

        $session->review($card, $validatedInput['remember']);

        if ($nextCard = $session->getNextCard()) {
            return new CardResource($nextCard);
        } else {
            $session->complete();
        }
    }
}
