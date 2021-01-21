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

        $box = Box::factory()->creator($user)->raw();
        
        $this->post("users/{$user->id}/boxes", $box)
            ->seeStatusCode(401)
            ->notSeeInDatabase('boxes', $box);
    }

    /** @test */
    public function users_can_only_create_boxes_for_themselves_not_for_other_users()
    {
        $box = Box::factory()->raw();
        
        $this->loginUser();
        
        $this->post("users/{$box['creator_id']}/boxes", $box)
            ->seeStatusCode(403)
            ->notSeeInDatabase('boxes', $box);
    }

    /** @test */
    public function users_can_update_their_boxes()
    {
        $box = Box::factory()->create();
        
        $attributes = Box::factory()->creator($box->creator)->raw();
        
        $this->loginUser($box->creator);
        
        $this->put("users/{$box->creator_id}/boxes/{$box->id}", $attributes)
            ->seeStatusCode(200)
            ->seeInDatabase('boxes', $attributes)
            ->seeJsonContains($attributes);
    }

    /** @test */
    public function guests_can_not_update_boxes()
    {
        $box = Box::factory()->create();

        $attributes = Box::factory()->creator($box->creator_id)->raw();
        
        $this->put("users/{$box->creator_id}/boxes/{$box->id}", $attributes)
            ->seeStatusCode(401)
            ->notSeeInDatabase('boxes', $attributes);
    }

    /** @test */
    public function users_can_only_update_their_own_boxes_not_other_users_boxes()
    {
        $box = Box::factory()->create();

        $attributes = Box::factory()->creator($box->creator_id)->raw();
        
        $this->loginUser();
        
        $this->put("users/{$box->creator_id}/boxes/{$box->id}", $attributes)
            ->seeStatusCode(403)
            ->notSeeInDatabase('boxes', $attributes);
    }

    /** @test */
    public function users_can_delete_their_boxes()
    {
        $box = Box::factory()->create();
        
        $this->loginUser($box->creator);
        
        $this->delete("users/{$box->creator_id}/boxes/{$box->id}")
            ->seeStatusCode(200)
            ->notSeeInDatabase('boxes', $box->makeHidden('creator')->toArray());
    }

    /** @test */
    public function guests_can_not_delete_boxes()
    {
        $box = Box::factory()->create();

        $this->delete("users/{$box->creator_id}/boxes/{$box->id}")
            ->seeStatusCode(401)
            ->seeInDatabase('boxes', $box->toArray());
    }

    /** @test */
    public function users_can_only_delete_their_own_boxes_not_other_users_boxes()
    {
        $box = Box::factory()->create();

        $this->loginUser();
        
        $this->delete("users/{$box->creator_id}/boxes/{$box->id}")
            ->seeStatusCode(403)
            ->seeInDatabase('boxes', $box->toArray());
    }

    /** @test */
    public function box_can_not_be_deleted_if_creator_has_a_session_on_it()
    {
        $box = Box::factory()->create();

        $box->createSession($box->creator);
        
        $this->loginUser($box->creator);
        
        $this->delete("users/{$box->creator_id}/boxes/{$box->id}")
            ->seeStatusCode(422)
            ->seeInDatabase('boxes', $box->makeHidden('creator')->toArray());
    }

    /** @test */
    public function box_can_not_be_deleted_if_other_users_have_sessions_on_it()
    {
        $box = Box::factory()->create();

        $user = User::factory()->create();
        
        $box->createSession($user);
        
        $this->loginUser($box->creator);
        
        $this->delete("users/{$box->creator_id}/boxes/{$box->id}")
            ->seeStatusCode(422)
            ->seeInDatabase('boxes', $box->makeHidden('creator')->toArray());
    }
}
