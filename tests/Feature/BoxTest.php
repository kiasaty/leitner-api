<?php

namespace Tests\Feature;

use App\Models\Box;
use App\Models\User;
use Tests\TestCase;

class BoxTest extends TestCase
{
    /** @test */
    public function users_can_create_boxes()
    {
        $user = User::factory()->create();
        
        $box = Box::factory()->creator($user)->raw();
        
        $this->loginUser($user);

        $this->post("api/users/{$user->id}/boxes", $box)
            ->assertStatus(201)
            ->assertJsonFragment($box);

        $this->assertDatabaseHas('boxes', $box);
    }

    /** @test */
    public function guests_can_not_create_boxes_for_users()
    {
        $user = User::factory()->create();

        $box = Box::factory()->creator($user)->raw();

        $this->post("api/users/{$user->id}/boxes", $box)
            ->assertStatus(401);

        $this->assertDatabaseMissing('boxes', $box);
    }

    /** @test */
    public function users_can_only_create_boxes_for_themselves_not_for_other_users()
    {
        $box = Box::factory()->raw();
        
        $this->loginUser();

        $this->post("api/users/{$box['creator_id']}/boxes", $box)
            ->assertStatus(403);

        $this->assertDatabaseMissing('boxes', $box);
    }

    /** @test */
    public function users_can_update_their_boxes()
    {
        $box = Box::factory()->create();
        
        $attributes = Box::factory()->creator($box->creator)->raw();
        
        $this->loginUser($box->creator);

        $this->put("api/users/{$box->creator_id}/boxes/{$box->id}", $attributes)
            ->assertStatus(200)
            ->assertJsonFragment($attributes);

        $this->assertDatabaseHas('boxes', $attributes);
    }

    /** @test */
    public function guests_can_not_update_boxes()
    {
        $box = Box::factory()->create();

        $attributes = Box::factory()->creator($box->creator_id)->raw();

        $this->put("api/users/{$box->creator_id}/boxes/{$box->id}", $attributes)
            ->assertStatus(401);

            $this->assertDatabaseMissing('boxes', $attributes);
    }

    /** @test */
    public function users_can_only_update_their_own_boxes_not_other_users_boxes()
    {
        $box = Box::factory()->create();

        $attributes = Box::factory()->creator($box->creator_id)->raw();
        
        $this->loginUser();
        
        $this->put("api/users/{$box->creator_id}/boxes/{$box->id}", $attributes)
            ->assertStatus(403);

        $this->assertDatabaseMissing('boxes', $attributes);
    }

    /** @test */
    public function users_can_delete_their_boxes()
    {
        $box = Box::factory()->create();
        
        $this->loginUser($box->creator);

        $this->delete("api/users/{$box->creator_id}/boxes/{$box->id}")
            ->assertStatus(200);

            $this->assertDatabaseMissing('boxes', $box->makeHidden('creator')->toArray());
    }

    /** @test */
    public function guests_can_not_delete_boxes()
    {
        $box = Box::factory()->create();

        $this->delete("api/users/{$box->creator_id}/boxes/{$box->id}")
            ->assertStatus(401);

        $this->assertDatabaseHas('boxes', $box->toArray());
    }

    /** @test */
    public function users_can_only_delete_their_own_boxes_not_other_users_boxes()
    {
        $box = Box::factory()->create();

        $this->loginUser();

        $this->delete("api/users/{$box->creator_id}/boxes/{$box->id}")
            ->assertStatus(403);

        $this->assertDatabaseHas('boxes', $box->toArray());
    }

    /** @test */
    public function box_can_not_be_deleted_if_creator_has_a_session_on_it()
    {
        $box = Box::factory()->create();

        $box->createSession($box->creator);
        
        $this->loginUser($box->creator);

        $this->delete("api/users/{$box->creator_id}/boxes/{$box->id}")
            ->assertStatus(422);

        $this->assertDatabaseHas('boxes', [
            'id' => $box->getKey(),
        ]);
    }

    /** @test */
    public function box_can_not_be_deleted_if_other_users_have_sessions_on_it()
    {
        $box = Box::factory()->create();

        $user = User::factory()->create();
        
        $box->createSession($user);
        
        $this->loginUser($box->creator);

        $this->delete("api/users/{$box->creator_id}/boxes/{$box->id}")
            ->assertStatus(422);

        $this->assertDatabaseHas('boxes', $box->makeHidden('creator')->toArray());
    }
}
