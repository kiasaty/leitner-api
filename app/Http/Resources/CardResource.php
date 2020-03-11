<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CardResource extends JsonResource
{
    /**
     * The additional meta data that should be added to the resource response.
     *
     * Added during response construction by the developer.
     *
     * @var array
     */
    public $additional = ['success' => true];
    
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'type'          => 'card',

            // Attributes
            'id'            => $this->id,
            'front'         => $this->front,
            'back'          => $this->back,
            'level'         => $this->level,
            'deck_id'       => $this->deck_id,
            'created_at'    => date_format($this->created_at, 'Y-m-d H:m:s'),
            'updated_at'    => date_format($this->updated_at, 'Y-m-d H:m:s'),
            
            // Relationships
            'box'           => new BoxResource($this->box),
        ];
    }
}
