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
     * The attributes that are searchable.
     *
     * @var array
     */
    public $searchables = ['front'];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new \App\Scopes\Search\Search);
    }

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
            ->withPivot(['level', 'deck', 'reviewed_at']);
    }
}
