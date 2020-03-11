<?php

namespace App\Http\Controllers;

use App\Box;
use Illuminate\Http\Request;
use App\Http\Resources\CardResource;

class BoxCardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'show']]);
    }

    /**
     * Get all cards
     *
     * @param  int  $boxID
     * @return \Illuminate\Http\Response
     */
    public function index($boxID)
    {
        $box = Box::findOrFail($boxID);
        
        return CardResource::collection($box->cards)
            ->additional(['success' => true ]);
    }

    /**
     * Create a card
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $boxID
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $boxID)
    {
        $box = Box::findOrFail($boxID);

        $validatedInput = $this->validateInput($request);

        $card = $box->cards()->create($validatedInput);

        return new CardResource($card);
    }

    /**
     * Get a card
     *
     * @param  int  $boxID
     * @param  int  $cardID
     * @return \Illuminate\Http\Response
     */
    public function show($boxID, $cardID)
    {
        $box = Box::findOrFail($boxID);

        $card = $box->cards()->findOrFail($cardID);

        return new CardResource($card);
    }

    /**
     * Edit a card.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $boxID
     * @param  int  $cardID
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $boxID, $cardID)
    {
        $box = Box::findOrFail($boxID);

        $card = $box->cards()->findOrFail($cardID);

        $validatedInput = $this->validateInput($request);

        $card->update($validatedInput);

        return new CardResource($card);
    }

    /**
     * Delete a card
     *
     * @param  int  $boxID
     * @param  int  $cardID
     * @return \Illuminate\Http\Response
     */
    public function destroy($boxID, $cardID)
    {
        $box = Box::findOrFail($boxID);

        $card = $box->cards()->findOrFail($cardID);

        $card->delete();
    }

    /**
     * Validates card's input
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return Array  validated input
     */
    private function validateInput($request)
    {
        $rules = [
            'front' => ['required', 'string',' min:2', 'max:250'],
            'back'  => ['required', 'string', 'min:3', 'max:1000']
        ];

        if ($request->isMethod('put')) {
            foreach($rules as $key => $value) {
                if ($rules[$key][0] === 'required') {
                    $rules[$key][0] = 'sometimes';
                }
            }
        }

        return $this->validate($request, $rules);
    }
}
