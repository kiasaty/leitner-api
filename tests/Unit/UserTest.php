<?php

namespace Tests\Unit;

use App\Box;
use App\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    /** @test */
    public function getSession()
    {
        $user = User::factory()->create();
        
        $box = Box::factory()->create([
            'creator_id' => $user->id
        ]);
        
        $session = $user->getSession($box->id);

        $this->assertEquals($session->user_id, $user->id);
        $this->assertEquals($session->box_id, $box->id);

        $userWhoIsNotCreatorOfBox = User::factory()->create();
        
        $session = $userWhoIsNotCreatorOfBox->getSession($box->id);

        $this->assertEquals($session->user_id, $userWhoIsNotCreatorOfBox->id);
        $this->assertEquals($session->box_id, $box->id);
    }
}
