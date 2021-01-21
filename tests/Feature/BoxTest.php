<?php

namespace Tests\Feature;

use App\Box;
use App\User;
use Tests\TestCase;

class BoxTest extends TestCase
{
    /** @test */
    public function users_can_create_boxes()
    {
        $user = User::factory()->create();
        
        $box = Box::factory()->creator($user)->raw();
        
        $this->loginUser($user);
        
        $this->post("users/{$user->id}/boxes", $box)
            ->seeStatusCode(201)
            ->seeInDatabase('boxes', $box)
            ->seeJsonContains($box);
    }

    /** @test */
    public function guests_can_not_create_boxes_for_users()
    {
        $user = User::factory()->create();

        $box = Box::factory()->raw();
        
        $this->post("users/{$user->id}/boxes", $box)
            ->seeStatusCode(401);
    }

    /** @test */
    public function users_can_only_create_boxes_for_themselves_not_for_other_users()
    {
        $creator = User::factory()->create();

        $anotherUser = User::factory()->create();

        $box = Box::factory()->raw();
        
        $this->loginUser($creator);
        
        $this->post("users/{$anotherUser->id}/boxes", $box)
            ->seeStatusCode(403);
    }
}
