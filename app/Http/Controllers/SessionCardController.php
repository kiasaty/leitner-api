<?php

namespace App\Http\Controllers;

use App\Http\Resources\CardResource;
use Illuminate\Http\Request;

class SessionCardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
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
