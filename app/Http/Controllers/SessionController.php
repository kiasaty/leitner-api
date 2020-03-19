<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SessionService;
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
            'box_id'    => 'required|numeric'
        ]);

        $box = $request->user()->boxes()->findOrFail(
            $validatedInput['box_id']
        );

        $result = (new SessionService($box))->start();

        return response()->json($result);
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

        $box = $request->user()->boxes()->findOrFail(
            $validatedInput['box_id']
        );

        $card = (new SessionService($box))->getNextCard();
        
        return new CardResource($card);
    }

    /**
     * Process the given card.
     * 
     * @todo   check if the given card is associated with the given box.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function process(Request $request)
    {
        $validatedInput = $this->validate($request, [
            'box_id'    => 'required|numeric',
            'card_id'   => 'required|numeric',
            'remember'  => 'required|boolean'
        ]);

        $box = $request->user()->boxes()->findOrFail(
            $validatedInput['box_id']
        );

        $card = $request->user()->cards()->findOrFail(
            $validatedInput['card_id']
        );

        $card = (new SessionService($box))->processCard($card);
        
        return response()->json($card);
    }
}
