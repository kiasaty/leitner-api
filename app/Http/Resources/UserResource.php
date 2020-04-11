<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'type'          => 'user',

            // Attributes
            'id'            => $this->id,
            'firstname'     => $this->firstname,
            'lastname'      => $this->lastname,
            'fullname'      => $this->fullname,
            'email'         => $this->email,
            'username'      => $this->username,
            'profile_photo' => $this->profile_photo,

            // Relationships
            
        ];
    }
}
