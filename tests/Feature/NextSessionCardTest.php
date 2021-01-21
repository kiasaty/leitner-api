<?php

namespace Tests\Feature;

use App\Box;
use App\Card;
use Tests\TestCase;

class NextSessionCardTest extends TestCase
{
    /** @test */
    public function guests_can_not_get_next_card_on_a_learning_session()
    {
        $box = Box::factory()->create();
        
        $this->get("boxes/{$box->id}/session/cards/next")
            ->seeStatusCode(401);
    }

    /** @test */
    public function users_can_get_next_card_of_their_learning_session()
    {
        $box = Box::factory()->create();
        $card = Card::factory()->create(['box_id' => $box->id]);
        $session = $box->createSession($box->creator);
        $session->addCards([$card->id]);
        $session->start();

        $this->loginUser($box->creator);

        $this->get("boxes/{$box->id}/session/cards/next")
            ->seeStatusCode(200)
            ->seeJsonContains(['id' => $card->id]);
    }

    /** @test */
    public function users_can_not_get_next_card_of_other_people_learning_session()
    {
        $box = Box::factory()->create();
        $card = Card::factory()->create(['box_id' => $box->id]);
        $session = $box->createSession($box->creator);
        $session->addCards([$card->id]);
        $session->start();

        $this->loginUser();

        $this->get("boxes/{$box->id}/session/cards/next")
            ->seeStatusCode(404);
    }

    /** @test */
    public function users_can_not_get_next_card_if_learning_session_does_not_exist()
    {
        $box = Box::factory()->create();

        $this->loginUser($box->creator);

        $this->get("boxes/{$box->id}/session/cards/next")
            ->seeStatusCode(404);
    }

    /** @test */
    public function users_can_not_get_next_card_if_learning_session_is_not_started()
    {
        $box = Box::factory()->create();
        $card = Card::factory()->create(['box_id' => $box->id]);
        $session = $box->createSession($box->creator);
        $session->addCards([$card->id]);

        $this->loginUser($box->creator);

        $this->get("boxes/{$box->id}/session/cards/next")
            ->seeStatusCode(422);
    }

    /** @test */
    public function users_can_not_get_next_card_if_learning_session_is_completed()
    {
        $box = Box::factory()->create();
        $card = Card::factory()->create(['box_id' => $box->id]);
        $session = $box->createSession($box->creator);
        $session->addCards([$card->id]);
        $session->start();
        $session->complete();

        $this->loginUser($box->creator);

        $this->get("boxes/{$box->id}/session/cards/next")
            ->seeStatusCode(422);
    }
}
