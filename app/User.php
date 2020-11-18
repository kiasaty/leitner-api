<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    
    /**
    * Get the user's full name.
    *
    * @return string
    */
    public function getFullnameAttribute()
    {
        return "{$this->firstname} {$this->lastname}";
    }

    /**
     * Check if the user is admin
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->id === 1;
    }

    /**
     * The boxes that the user has created.
     */
    public function createdBoxes()
    {
        return $this->hasMany('App\Box', 'creator_id');
    }

    /**
     * The boxes that the user is learning.
     */
    public function boxes()
    {
        return $this->belongsToMany('App\Box', 'sessions')
            ->as('session')
            ->withPivot(['number', 'started_at']);
    }

    /**
     * The learning sessions associated with the user.
     */
    public function sessions()
    {
        return $this->hasMany('App\Session');
    }
    
    /**
     * Get the user's session by boxID.
     *
     * @param  int  $boxID
     * @return \App\Box
     */
    public function getSessionByBoxID($boxID)
    {
        return $this->sessions()->firstOrCreate(['box_id' => $boxID]);
    }
    
    /**
     * Get the user's card.
     *
     * @param  int  $cardID
     * @return \App\Card
     */
    public function getCard($cardID)
    {
        return $this->cards()->findOrFail($cardID);
    }
}
