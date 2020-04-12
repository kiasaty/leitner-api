<?php

namespace App\Policies;

use App\User;
use App\Session;

class SessionPolicy extends Policy
{
    /**
     * Determine if the user is allowed to update the session.
     *
     * @param  \App\User      $user
     * @param  \App\Session   $session
     * @return bool
     */
    public function update(User $user, Session $session)
    {
        return $user->id == $session->user_id;
    }
}
