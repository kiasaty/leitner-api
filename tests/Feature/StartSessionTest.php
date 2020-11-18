<?php

namespace Tests\Feature;

use App\Box;
use Tests\TestCase;

class StartSessionTest extends TestCase
{
    /** @test */
    public function users_can_start_a_session_on_their_own_boxes()
    {
        $box = Box::factory()->hasCards(5)->create();

        $this->loginUser($box->creator);
        
        $this->post("boxes/{$box->id}/session/start")
            ->seeStatusCode(200);
    }

    /** @test */
    public function users_can_start_a_session_on_other_users_boxes()
    {
        $box = Box::factory()->hasCards(5)->create();

        $this->loginUser();

        $this->post("boxes/{$box->id}/session/start")
            ->seeStatusCode(200);
    }

    /** @test */
    public function session_can_not_be_started_when_box_is_empty()
    {
        $box = Box::factory()->create();

        $this->loginUser($box->creator);

        $this->post("boxes/{$box->id}/session/start")
            ->seeStatusCode(422);
    }
}
