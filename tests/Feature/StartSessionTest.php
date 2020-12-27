<?php

namespace Tests\Feature;

use App\Box;
use App\Card;
use Tests\TestCase;

class StartSessionTest extends TestCase
{
    /** @test */
    public function guests_can_not_start_a_new_session()
    {
        $box = Box::factory()->hasCards(5)->create();

        $this->post("boxes/{$box->id}/session/start")
            ->seeStatusCode(401);
    }
    
    /** @test */
    public function users_can_start_a_session_on_their_own_boxes()
    {
        $box = Box::factory()->hasCards(5)->create();
        
        $session = $box->createSession($box->creator_id);

        $this->loginUser($box->creator);
        
        $this->post("boxes/{$box->id}/session/start")
            ->seeStatusCode(200);
        
        $session->refresh();
            
        $this->assertTrue($session->isRunning());
    }

    /** @test */
    public function users_can_start_a_session_on_other_users_boxes()
    {
        $box = Box::factory()->hasCards(5)->create();

        $user = $this->loginUser();

        $session = $box->createSession($user);

        $this->post("boxes/{$box->id}/session/start")
            ->seeStatusCode(200);
        
        $session->refresh();
                
        $this->assertTrue($session->isRunning());
    }

    /** @test */
    public function new_session_can_not_be_started_when_box_is_empty()
    {
        $box = Box::factory()->create();

        $session = $box->createSession($box->creator_id);

        $this->loginUser($box->creator);

        $this->post("boxes/{$box->id}/session/start")
            ->seeStatusCode(422);
        
        $session->refresh();
                    
        $this->assertFalse($session->isRunning());
    }

    /** @test */
    public function new_session_can_not_be_started_when_all_cards_in_box_are_studied()
    {
        $box = Box::factory()->hasCards(5)->create();

        $session = $box->createSession($box->creator_id);

        $session->addCards(
            $box->cards->pluck('id'),
            ['level' => 5, 'deck_id' => 12]
        );

        $this->loginUser($box->creator);

        $this->post("boxes/{$box->id}/session/start")
            ->seeStatusCode(422);
        
        $session->refresh();
                        
        $this->assertFalse($session->isRunning());
    }

    /** @test */
    public function new_session_can_be_started_when_there_are_still_none_retired_cards_in_session()
    {
        $box = Box::factory()->hasCards(5)->create();

        $session = $box->createSession($box->creator_id);

        $session->addCards(
            $box->cards->pluck('id'),
            ['level' => 5, 'deck_id' => 12]
        );

        $session->addCards(
            Card::factory()->count(5)->create(['box_id' => $session->box_id])->pluck('id')
        );

        $this->loginUser($box->creator);

        $this->post("boxes/{$box->id}/session/start")
            ->seeStatusCode(200);
                         
        $session->refresh();
            
        $this->assertTrue($session->isRunning());
    }

    /** @test */
    public function new_session_can_be_started_when_all_cards_in_session_are_retired_but_there_are_cards_left_in_box()
    {
        $box = Box::factory()->hasCards(5)->create();

        $session = $box->createSession($box->creator_id);

        $session->addCards(
            $box->cards->take(3)->pluck('id'),
            ['level' => 5, 'deck_id' => 12]
        );

        $this->loginUser($box->creator);

        $this->post("boxes/{$box->id}/session/start")
            ->seeStatusCode(200);
                         
        $session->refresh();
                
        $this->assertTrue($session->isRunning());
    }

    public function new_session_can_be_started_only_when_the_previous_session_is_completed()
    {
        //
    }

    /** @test */
    public function new_session_can_be_started_only_when_the_break_time_is_over()
    {
        $box = Box::factory()->hasCards(5)->create();

        $session = $box->createSession($box->creator_id);

        $session->addCards(
            $box->cards->take(3)->pluck('id'),
            ['level' => 5, 'deck_id' => 12]
        );

        $session->complete();

        $this->loginUser($box->creator);

        $this->post("boxes/{$box->id}/session/start")
            ->seeStatusCode(422);

        $session->refresh();

        $this->assertFalse($session->isRunning());
        
        // $breakTimeBetweenSessions = config('session.gap_time');

        // $this->travel($breakTimeBetweenSessions + 1)->minutes();

        // $this->post("boxes/{$box->id}/session/start")
        //     ->seeStatusCode(200);

        // $this->assertTrue($session->isRunning());
    }
}
