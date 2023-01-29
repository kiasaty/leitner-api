<?php

namespace Tests\Feature;

use App\Models\Box;
use App\Models\Card;
use Tests\TestCase;

class NextSessionCardTest extends TestCase
{
    /** @test */
    public function guests_can_not_get_next_card_on_a_learning_session()
    {
        $box = Box::factory()->create();
        
        $this->get("api/boxes/{$box->id}/session/cards/next")
            ->assertStatus(401);
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

        $this->get("api/boxes/{$box->id}/session/cards/next")
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $card->id]);
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

        $this->get("api/boxes/{$box->id}/session/cards/next")
            ->assertStatus(404);
    }

    /** @test */
    public function users_can_not_get_next_card_if_learning_session_does_not_exist()
    {
        $box = Box::factory()->create();

        $this->loginUser($box->creator);

        $this->get("api/boxes/{$box->id}/session/cards/next")
            ->assertStatus(404);
    }

    /** @test */
    public function users_can_not_get_next_card_if_learning_session_is_not_started()
    {
        $box = Box::factory()->create();
        $card = Card::factory()->create(['box_id' => $box->id]);
        $session = $box->createSession($box->creator);
        $session->addCards([$card->id]);

        $this->loginUser($box->creator);

        $this->get("api/boxes/{$box->id}/session/cards/next")
            ->assertStatus(422);
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

        $this->get("api/boxes/{$box->id}/session/cards/next")
            ->assertStatus(422);
    }
}
