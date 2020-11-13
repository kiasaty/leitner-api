<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\CardResource;

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
     * @return \Illuminate\Http\Response
     */
    public function start(Request $request)
    {
        $validatedInput = $this->validate($request, [
            'box_id' => 'required|numeric|exists:boxes,id'
        ]);

        $session = $request->user()->getSessionByBoxID($validatedInput['box_id']);

        $this->authorize('update', $session);

        $session->start();

        if ($nextCard = $session->getNextCard()) {
            return new CardResource($nextCard);
        }
    }

    /**
     * Get the next card.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function next(Request $request)
    {
        $validatedInput = $this->validate($request, [
            'box_id'    => 'required|numeric'
        ]);

        $session = $request->user()->getSessionByBoxID($validatedInput['box_id']);

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
     * @todo   check if the given card is associated with the given box.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function review(Request $request)
    {
        $validatedInput = $this->validate($request, [
            'box_id'    => 'required|numeric',
            'card_id'   => 'required|numeric',
            'remember'  => 'required|boolean'
        ]);

        $user = $request->user();
        
        $session = $user->getSessionByBoxID($validatedInput['box_id']);

        $this->authorize('update', $session);

        $card = $user->getCard($validatedInput['card_id']);

        $session->review($card, $validatedInput['remember']);

        if ($nextCard = $session->getNextCard()) {
            return new CardResource($nextCard);
        } else {
            $session->end();
        }
    }
}
