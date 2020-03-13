<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Http\Resources\BoxResource;

class UserBoxController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'show']]);
    }

    /**
     * Get all boxes
     *
     * @param  int  $userID
     * @return \Illuminate\Http\Response
     */
    public function index($userID)
    {
        $user = User::findOrFail($userID);

        $boxes = $user->createdBoxes;
        
        return BoxResource::collection($boxes)
            ->additional(['success' => true ]);
    }

    /**
     * Create a box
     * 
     * @param  int  $userID
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $userID)
    {
        $validatedInput = $this->validateInput($request);

        $user = User::findOrFail($userID);

        $box = $user->createdBoxes()->create($validatedInput);

        $user->boxes()->attach($box->id);

        return new BoxResource($box);
    }

    /**
     * Get a box
     *
     * @param  int  $userID
     * @param  int  $boxID
     * @return \Illuminate\Http\Response
     */
    public function show($userID, $boxID)
    {
        $user = User::findOrFail($userID);

        $box = $user->createdBoxes()->findOrFail($boxID);

        return new BoxResource($box);
    }

    /**
     * Edit a box.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $userID
     * @param  int  $boxID
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $userID, $boxID)
    {
        $user = User::findOrFail($userID);

        $box = $user->createdBoxes()->findOrFail($boxID);

        $validatedInput = $this->validateInput($request);

        $box->update($validatedInput);

        return new BoxResource($box);
    }

    /**
     * Delete a box
     *
     * @param  int  $userID
     * @param  int  $boxID
     * @return \Illuminate\Http\Response
     */
    public function destroy($userID, $boxID)
    {
        $user = User::findOrFail($userID);

        $box = $user->createdBoxes()->findOrFail($boxID);

        $box->delete();
    }

    /**
     * Validates box's input
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return Array  validated input
     */
    private function validateInput($request)
    {
        $rules = [
            'title'         => ['required', 'string',' min:2', 'max:250'],
            'description'   => ['required', 'string', 'min:3', 'max:1000']
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
