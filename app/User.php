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
     * Create a session for the user on the given box.
     *
     * @param  int|\App\Box  $box
     * @return \App\Session
     */
    public function createSession($box)
    {
        return $this->sessions()->create([
            'box_id' => is_numeric($box) ? $box : $box->id
        ]);
    }
    
    /**
     * Get the user's session on the given box.
     *
     * @todo rename this to findSessionOfFail
     *
     * @param  int|\App\Box  $box
     * @return \App\Session
     */
    public function getSession($box)
    {
        $boxID = is_numeric($box) ? $box : $box->id;

        return $this->sessions()->where('box_id', $boxID)->firstOrFail();
    }
}
