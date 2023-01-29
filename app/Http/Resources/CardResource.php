<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Session;

class CardResource extends JsonResource
{
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
            'created_at'    => date_format($this->created_at, 'Y-m-d H:m:s'),
            'updated_at'    => date_format($this->updated_at, 'Y-m-d H:m:s'),

            'level'         => $this->whenPivotLoadedAs('progress', 'session_card', function () {
                return $this->progress->level;
            }),
            'deck'          => $this->whenPivotLoadedAs('progress', 'session_card', function () {
                return Session::DECKS[$this->progress->deck_id];
            }),
            'reviewed_at'   => $this->whenPivotLoadedAs('progress', 'session_card', function () {
                return $this->progress->reviewed_at;
            }),
            
            // Relationships
            // 'box'   => new BoxResource($this->box),
        ];
    }
}
