<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Session;

class SessionPolicy extends Policy
{
    /**
     * Determine if the user is allowed to update the session.
     *
     * @param  \App\Models\User      $user
     * @param  \App\Models\Session   $session
     * @return bool
     */
    public function update(User $user, Session $session)
    {
        return $user->id == $session->user_id;
    }
}
