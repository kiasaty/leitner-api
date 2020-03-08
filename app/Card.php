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
}
