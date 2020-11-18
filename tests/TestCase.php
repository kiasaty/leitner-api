<?php

namespace Tests;

use App\User;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    /**
     * Login a user.
     *
     * @param  \App\User
     * @return \App\User
     */
    public function loginUser(User $user = null)
    {
        $user ??= User::factory()->create();

        $this->actingAs($user);

        return $user;
    }
}
