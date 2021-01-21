<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BoxResource extends JsonResource
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
            'type'          => 'box',

            // Attributes
            'id'            => $this->id,
            'creator_id'    => $this->creator_id,
            'title'         => $this->title,
            'description'   => $this->description,
            'created_at'    => date_format($this->created_at, 'Y-m-d H:m:s'),
            'updated_at'    => date_format($this->updated_at, 'Y-m-d H:m:s'),
            
            // Relationships
            'creator'       => new UserResource($this->creator),
        ];
    }
}
