<?php

namespace App\Policies;

use App\User;
use App\Box;

class BoxPolicy extends Policy
{
    /**
     * Determine if the user is allowed to update the box.
     *
     * @param  \App\User  $user
     * @param  int  $userID
     * @return bool
     */
    public function store(User $user, $userID)
    {
        return $user->id == $userID;
    }

    /**
     * Determine if the user is allowed to update the box.
     *
     * @param  \App\User  $user
     * @param  \App\Box  $box
     * @return bool
     */
    public function update(User $user, Box $box)
    {
        return $user->id == $box->creator_id;
    }
}
