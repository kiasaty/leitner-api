<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get the box of this card.
     */
    public function box()
    {
        return $this->belongsTo('App\Box');
    }

    /**
     * The users that are using the card.
     */
    public function users()
    {
        return $this->belongsToMany('App\User')
            ->as('progress')
            ->withPivot(['level', 'deck_id', 'reviewed_at']);
    }
}
