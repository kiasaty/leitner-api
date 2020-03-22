<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

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
        return $this->belongsToMany('App\Box')
            ->as('subscription')
            ->withPivot(['session', 'session_started_at']);
    }

    /**
     * The cards that this user is using.
     */
    public function cards()
    {
        return $this->belongsToMany('App\Card')
            ->as('progress')
            ->withPivot(['level', 'deck', 'reviewed_at']);
    }
}
