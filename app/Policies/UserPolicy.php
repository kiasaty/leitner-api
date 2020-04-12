<?php

namespace App\Policies;

use App\User;

class UserPolicy extends Policy
{
    /**
     * Determine if the user is allowed to list users.
     *
     * @return bool
     */
    public function index()
    {
        return false;
    }

    /**
     * Determine if the user is allowed to see the user.
     *
     * @param  \App\User  $user
     * @return bool
     */
    public function show(User $user)
    {
        return $user->id == request('id');
    }

    /**
     * Determine if the user is allowed to update the user.
     *
     * @param  \App\User  $user
     * @return bool
     */
    public function update(User $user)
    {
        return $user->id == request('id');
    }

    /**
     * Determine if the user is allowed to destroy the user.
     *
     * @return bool
     */
    public function destroy()
    {
        return false;
    }
}
