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
     * Get the user's boxes.
     */
    public function boxes()
    {
        return $this->hasMany('App\Box', 'creator_id');
    }
}
