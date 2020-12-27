<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Box extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get the creator of this box.
     */
    public function creator()
    {
        return $this->belongsTo('App\User', 'creator_id');
    }

    /**
     * The users that are using the box.
     */
    public function users()
    {
        return $this->belongsToMany('App\User', 'sessions')
            ->as('session')
            ->withPivot(['number', 'started_at']);
    }

    /**
     * Get the cards of this box.
     */
    public function cards()
    {
        return $this->hasMany('App\Card');
    }

    /**
     * The learning sessions associated with the box.
     */
    public function sessions()
    {
        return $this->hasMany('App\Session');
    }

    /**
     * Create a session on the box for the user.
     *
     * @param  \App\User|int  $user
     * @return \App\Session
     */
    public function createSession($user)
    {
        return $this->sessions()->create([
            'user_id' => is_numeric($user) ? $user : $user->id
        ]);
    }

    /**
     * Get a session associated with the box for the user.
     *
     * @todo rename this to findSessionOfFail
     * 
     * @param  \App\User|int  $user
     * @return \App\Session
     */
    public function getSession($user)
    {
        $userID = is_numeric($user) ? $user : $user->id;

        return $this->sessions()->where('user_id', $userID)->firstOrFail();
    }
}
