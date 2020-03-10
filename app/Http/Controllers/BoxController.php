<?php

namespace App\Http\Controllers;

use App\Box;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Resources\BoxResource;

class BoxController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'show']]);
    }

    /**
     * Get all boxes
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $boxes = Box::all();
        
        return BoxResource::collection($boxes)
            ->additional(['success' => true ]);
    }

    /**
     * Create a box
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedInput = $this->validateInput($request);

        $box = $request->user()->boxes()->create($validatedInput);

        return new BoxResource($box);
    }

    /**
     * Get a box
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $box = Box::findOrFail($id);

        return new BoxResource($box);
    }

    /**
     * Edit a box.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $box = $request->user()->boxes()->findOrFail($id);

        $validatedInput = $this->validateInput($request);

        $box->update($validatedInput);

        return new BoxResource($box);
    }

    /**
     * Delete a box
     *
     * @param int $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $box = $request->user()->boxes()->findOrFail($id);

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
