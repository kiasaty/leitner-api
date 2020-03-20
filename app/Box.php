<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Box extends Model
{
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
        return $this->belongsToMany('App\User')
            ->as('subscription')
            ->withPivot(['session', 'session_started_at']);
    }

    /**
     * Get the cards of this box.
     */
    public function cards()
    {
        return $this->hasMany('App\Card');
    }
}
