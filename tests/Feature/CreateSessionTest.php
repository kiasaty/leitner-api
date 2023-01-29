<?php

namespace Tests\Feature;

use App\Models\Box;
use Tests\TestCase;

class CreateSessionTest extends TestCase
{
    /** @test */
    public function guests_can_not_create_sessions()
    {
        $box = Box::factory()->create();
        
        $this->post("api/boxes/{$box->id}/session/create")
            ->assertStatus(401);
    }
    
    /** @test */
    public function users_can_create_session_on_their_own_boxes()
    {
        $box = Box::factory()->create();
        
        $this->loginUser($box->creator);

        $this->post("api/boxes/{$box->id}/session/create")
            ->assertStatus(200);

        $this->assertDatabaseHas('sessions', [
            'box_id' => $box->id,
            'user_id' => $box->creator_id,
        ]);
    }

    /** @test */
    public function users_can_create_session_on_other_people_boxes()
    {
        $box = Box::factory()->create();
        
        $user = $this->loginUser();

        $this->post("api/boxes/{$box->id}/session/create")
            ->assertStatus(200);

        $this->assertDatabaseHas('sessions', [
            'box_id' => $box->id,
            'user_id' => $user->id,
        ]);
    }
}
