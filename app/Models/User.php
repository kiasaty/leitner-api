<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
    
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
    public function boxes()
    {
        return $this->hasMany('App\Models\Box', 'creator_id');
    }

    /**
     * The learning sessions associated with the user.
     */
    public function sessions()
    {
        return $this->hasMany('App\Models\Session');
    }
    
    /**
     * Create a session for the user on the given box.
     *
     * @param  int|\App\Models\Box  $box
     * @return \App\Models\Session
     */
    public function createSession($box)
    {
        return $this->sessions()->create([
            'box_id' => is_numeric($box) ? $box : $box->id
        ]);
    }
    
    /**
     * Check if the user has a session on the given box.
     *
     * @param  int|\App\Models\Box  $box
     * @return bool
     */
    public function hasSession($box)
    {
        return $this->sessions()
            ->where('box_id', is_numeric($box) ? $box : $box->id)
            ->exists();
    }
    
    /**
     * Get the user's session on the given box.
     *
     * @todo rename this to findSessionOfFail
     *
     * @param  int|\App\Models\Box  $box
     * @return \App\Models\Session
     */
    public function getSession($box)
    {
        $boxID = is_numeric($box) ? $box : $box->id;

        return $this->sessions()->where('box_id', $boxID)->firstOrFail();
    }
}
