<?php

namespace Tests\Unit;

use App\Box;
use App\User;
use Tests\TestCase;

class BoxTest extends TestCase
{
    /** @test */
    public function getSession()
    {
        $box = Box::factory()->create();
        
        $session = $box->getSession($box->creator_id);

        $this->assertEquals($session->user_id, $box->creator_id);
        $this->assertEquals($session->box_id, $box->id);

        $userWhoIsNotCreatorOfBox = User::factory()->create();
        
        $session = $box->getSession($userWhoIsNotCreatorOfBox->id);

        $this->assertEquals($session->user_id, $userWhoIsNotCreatorOfBox->id);
        $this->assertEquals($session->box_id, $box->id);
    }
}
