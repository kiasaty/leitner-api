<?php

namespace Tests\Feature;

use App\Box;
use Tests\TestCase;

class ReviewCardTest extends TestCase
{
    /** @test */
    public function guests_can_not_review_cards()
    {
        $box = Box::factory()->hasCards(1)->create();
        
        $this->post("boxes/{$box->id}/session/cards/{$box->cards->first()->id}/review", ['remember' => true])
            ->seeStatusCode(401);
    }
    
    /** @test */
    public function card_can_not_be_reviewed_if_box_does_not_exist()
    {
        $this->loginUser();

        $this->post("boxes/1000/session/cards/1/review", ['remember' => true])
            ->seeStatusCode(404);
    }
    
    /** @test */
    public function card_can_not_be_reviewed_if_card_does_not_exist_in_box()
    {
        $box = Box::factory()->create();

        $this->loginUser();

        $this->post("boxes/{$box->id}/session/cards/1000/review", ['remember' => true])
            ->seeStatusCode(404);
    }

    /** @test */
    public function card_can_not_be_reviewed_if_card_does_not_belong_to_the_given_box()
    {
        $box = Box::factory()->hasCards(1)->create();

        $anotherBox = Box::factory()->hasCards(1)->create();

        $this->loginUser();
        
        $this->post("boxes/{$anotherBox->id}/session/cards/{$box->cards->first()->id}/review", ['remember' => true])
            ->seeStatusCode(404);
    }
    
    /** @test */
    public function card_can_not_be_reviewed_if_card_does_not_exist_in_session()
    {
        $box = Box::factory()->hasCards(1)->create();

        $box->createSession($box->creator);

        $this->loginUser($box->creator);

        $this->post("boxes/{$box->id}/session/cards/{$box->cards->first()->id}/review", ['remember' => true])
            ->seeStatusCode(404);
    }

    /** @test */
    public function card_can_not_be_reviewed_if_user_has_no_session_on_the_given_box()
    {
        $box = Box::factory()->hasCards(1)->create();

        $this->loginUser();

        $this->post("boxes/{$box->id}/session/cards/{$box->cards->first()->id}/review", ['remember' => true])
            ->seeStatusCode(404);
    }
}
