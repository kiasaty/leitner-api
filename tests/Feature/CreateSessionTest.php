<?php

namespace Tests\Feature;

use App\Box;
use Tests\TestCase;

class CreateSessionTest extends TestCase
{
    /** @test */
    public function users_can_create_session_on_their_own_boxes()
    {
        $box = Box::factory()->hasCards(5)->create();
        
        $this->loginUser($box->creator);
        
        $this->post("boxes/{$box->id}/session/create")
            ->seeStatusCode(200)
            ->seeInDatabase('sessions', [
                'box_id' => $box->id,
                'user_id' => $box->creator_id,
            ]);
    }

    /** @test */
    public function users_can_create_session_on_other_people_boxes()
    {
        $box = Box::factory()->hasCards(5)->create();
        
        $user = $this->loginUser();
        
        $this->post("boxes/{$box->id}/session/create")
            ->seeStatusCode(200)
            ->seeInDatabase('sessions', [
                'box_id' => $box->id,
                'user_id' => $user->id,
            ]);
    }
}
