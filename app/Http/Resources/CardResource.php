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
        $test = 'test';
        return [
            'type'          => 'card',

            // Attributes
            'id'            => $this->id,
            'front'         => $this->front,
            'back'          => $this->back,
            'created_at'    => date_format($this->created_at, 'Y-m-d H:m:s'),
            'updated_at'    => date_format($this->updated_at, 'Y-m-d H:m:s'),

            'level'     => $this->whenPivotLoadedAs('progress', 'card_user', function () {
                return $this->progress->level;
            }),
            'deck_id'   => $this->whenPivotLoadedAs('progress', 'card_user', function () {
                return $this->progress->deck_id;
            }),
            
            // Relationships
            'box'   => new BoxResource($this->box),
        ];
    }
}
