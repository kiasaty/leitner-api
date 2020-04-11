<?php

namespace App\Http\Controllers;

use App\Box;
use App\Http\Resources\BoxResource;

class BoxController extends Controller
{
    /**
     * Get all boxes
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $boxes = Box::all();
        
        return BoxResource::collection($boxes);
    }

    /**
     * Get a box
     *
     * @param  int  $boxID
     * @return \Illuminate\Http\Response
     */
    public function show($boxID)
    {
        $box = Box::findOrFail($boxID);

        return new BoxResource($box);
    }
}
