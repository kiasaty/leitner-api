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
            'box_id'    => 'required|numeric|exists:boxes,id'
        ]);

        $session = $request->user()->sessions()
            ->where('box_id', $validatedInput['box_id'])->first();

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

        $session = $request->user()->sessions()
            ->where('box_id', $validatedInput['box_id'])->first();

        $this->authorize('update', $session);

        $card = $session->getNextCard();

        if (is_null($card)) {
            abort(422, 'The current session is completed!');
        }
        
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

        $session = $request->user()->sessions()
            ->where('box_id', $validatedInput['box_id'])->first();

        $this->authorize('update', $session);

        $card = $request->user()->cards()->findOrFail(
            $validatedInput['card_id']
        );

        $session->review($card, $validatedInput['remember']);

        if ($nextCard = $session->getNextCard()) {
            return new CardResource($nextCard);
        }
    }
}
