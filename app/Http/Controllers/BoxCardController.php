<?php

namespace App\Http\Controllers;

use App\Models\Box;
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
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $boxID
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $boxID)
    {
        $box = Box::findOrFail($boxID);

        $this->validate($request, [
            'page'      => 'nullable|numeric',
            'per_page'  => 'nullable|numeric',
            'q'         => 'nullable|string',
            'sort'      => 'nullable|in:id,-id,front,-front,created_at,-created_at,updated_at,-updated_at'
        ]);

        $cards = $box->cards()->latest()->paginate(
            $request->query('per_page')
        );
        
        return CardResource::collection($cards);
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

        $this->authorize('update', $box);

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

        $this->authorize('update', $box);

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

        $this->authorize('update', $box);

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
            'back'  => ['required', 'string', 'min:2']
        ];

        if ($request->isMethod('put')) {
            foreach ($rules as $key => $value) {
                if ($rules[$key][0] === 'required') {
                    $rules[$key][0] = 'sometimes';
                }
            }
        }

        return $this->validate($request, $rules);
    }
}
