<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Box;

class BoxPolicy extends Policy
{
    /**
     * Determine if the user is allowed to update the box.
     *
     * @param  \App\Models\User  $user
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
     * @param  \App\Models\User  $user
     * @param  \App\Models\Box  $box
     * @return bool
     */
    public function update(User $user, Box $box)
    {
        return $user->id == $box->creator_id;
    }
}
