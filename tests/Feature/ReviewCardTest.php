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
    
    public function card_can_not_be_reviewed_if_session_is_not_in_running_state()
    {
        $box = Box::factory()->hasCards(3)->create();

        $session = $box->createSession($box->creator);

        $session->addCards($box->cards->pluck('id'));

        $this->loginUser($box->creator);

        $this->post("boxes/{$box->id}/session/cards/{$box->cards->first()->id}/review", ['remember' => true])
            ->seeStatusCode(422);

        $session->start();

        $session->complete();

        $this->post("boxes/{$box->id}/session/cards/{$box->cards->first()->id}/review", ['remember' => true])
            ->seeStatusCode(422);
    }
    
    public function card_can_not_be_reviewed_if_card_is_retired()
    {
        $box = Box::factory()->hasCards(1)->create();

        $session = $box->createSession($box->creator);

        $session->start();

        $session->addCards(
            $box->cards->pluck('id'),
            ['level' => 5, 'deck_id' => 12]
        );

        $this->loginUser($box->creator);

        $this->post("boxes/{$box->id}/session/cards/{$box->cards->first()->id}/review", ['remember' => true])
            ->seeStatusCode(422);
    }
    
    public function card_can_be_reviewed_only_if_card_deck_is_in_current_session_decks()
    {
        $box = Box::factory()->hasCards(1)->create();

        $session = $box->createSession($box->creator);

        $session->start();

        /**
         * When a session starts for the first time, its number is 0.
         * The first deck, which does not include number 0, is the deck with id 4.
         * A card with a different deck with the current session deck is added to the session.
         */
        $session->addCards(
            $box->cards->pluck('id'),
            ['level' => 2, 'deck_id' => 4]
        );

        $this->loginUser($box->creator);

        $this->post("boxes/{$box->id}/session/cards/{$box->cards->first()->id}/review", ['remember' => true])
            ->seeStatusCode(422);
    }

    public function card_can_be_reviewed_if_it_has_not_been_reviewed_in_current_session_yet()
    {
        $box = Box::factory()->hasCards(3)->create();

        $session = $box->getSession($box->creator_id);

        $session->addCards(
            $box->cards->pluck('id')
        );

        $session->start();

        $cardToReview = $session->getNextCardToReview();
        
        $session->promoteCard($cardToReview);
            
        $this->post("boxes/{$box->id}/session/cards/{$cardToReview->id}/review", $attributes)
            ->seeStatusCode(422);

        $cardToReview = $session->getNextCardToReview();
        
        $session->demoteCard($cardToReview);
            
        $this->post("boxes/{$box->id}/session/cards/{$cardToReview->id}/review", $attributes)
            ->seeStatusCode(422);
    }

    public function a_card_will_be_promoted_if_user_remembers_it()
    {
        $box = Box::factory()->hasCards(1)->create();

        $session = $box->getSession($box->creator_id);

        $session->addCards(
            $box->cards->pluck('id')
        );

        $session->start();

        $cardToReview = $session->getNextCardToReview();

        $this->post("boxes/{$box->id}/session/cards/{$cardToReview->id}/review", ['remember' => true])
            ->seeStatusCode(200);
        
        $previousLevel = $cardToReview->level;
        $cardToReview->refresh();
        $newLevel = $cardToReview->level;
        $this->assertEquals($newLevel, $previousLevel + 1);
    }

    public function a_card_will_be_demoted_if_user_does_not_remember_it()
    {
        $box = Box::factory()->hasCards(1)->create();

        $session = $box->getSession($box->creator_id);

        $session->addCards(
            $box->cards->pluck('id'),
            ['level' => 3, 'deck_id' => 2]
        );

        $session->start();

        $cardToReview = $session->getNextCardToReview();

        $this->post("boxes/{$box->id}/session/cards/{$cardToReview->id}/review", ['remember' => false])
            ->seeStatusCode(200);
        
        $cardToReview->refresh();
        
        $this->assertEquals($cardToReview->level, 1);
        $this->assertEquals($cardToReview->deck_id, 1);
    }

    public function when_user_does_not_remember_a_card_the_difficulty_of_card_increases()
    {
        $box = Box::factory()->hasCards(1)->create();

        $session = $box->getSession($box->creator_id);

        $session->addCards(
            $box->cards->pluck('id'),
            ['level' => 3, 'deck_id' => 2]
        );

        $session->start();

        $cardToReview = $session->getNextCardToReview();

        $this->post("boxes/{$box->id}/session/cards/{$cardToReview->id}/review", ['remember' => false])
            ->seeStatusCode(200);

        $previousDifficulty = $cardToReview->difficulty;
        $cardToReview->refresh();
        
        $this->assertEquals($cardToReview->difficulty, $previousDifficulty + 1);
    }

    public function when_user_remembers_a_card_the_difficulty_of_card_does_not_change()
    {
        $box = Box::factory()->hasCards(1)->create();

        $session = $box->getSession($box->creator_id);

        $session->addCards(
            $box->cards->pluck('id'),
            ['level' => 3, 'deck_id' => 2]
        );

        $session->start();

        $cardToReview = $session->getNextCardToReview();

        $this->post("boxes/{$box->id}/session/cards/{$cardToReview->id}/review", ['remember' => true])
            ->seeStatusCode(200);

        $previousDifficulty = $cardToReview->difficulty;
        $cardToReview->refresh();
        
        $this->assertEquals($cardToReview->difficulty, $previousDifficulty);
    }

    public function when_a_card_gets_promoted_for_the_first_time_it_gets_the_current_session_deck()
    {
        $box = Box::factory()->hasCards(1)->create();

        $session = $box->getSession($box->creator_id);

        $session->addCards(
            $box->cards->pluck('id')
        );

        $session->start();

        $cardToReview = $session->getNextCardToReview();

        $this->post("boxes/{$box->id}/session/cards/{$cardToReview->id}/review", ['remember' => true])
            ->seeStatusCode(200);
        
        $cardToReview->refresh();

        $this->assertEquals($cardToReview->deck_id, $session->deck_id);

        $session->complete();
        $session->start();

        $cardToReview = $session->getNextCardToReview();

        $this->post("boxes/{$box->id}/session/cards/{$cardToReview->id}/review", ['remember' => true])
            ->seeStatusCode(200);
        
        $previousDeckID = $cardToReview->deck_id;

        $cardToReview->refresh();

        $this->assertEquals($cardToReview->deck_id, $previousDeckID);
    }

    public function when_a_card_is_reviewed_the_reviewed_at_column_is_updated()
    {
        // maybe this should be a unit test: testGetSessionUpdatesReviewedAt()
        
        $box = Box::factory()->hasCards(1)->create();

        $session = $box->getSession($box->creator_id);

        $session->addCards(
            $box->cards->pluck('id')
        );

        $session->start();

        $cardToReview = $session->getNextCardToReview();

        $this->post("boxes/{$box->id}/session/cards/{$cardToReview->id}/review", ['remember' => true])
            ->seeStatusCode(200);
        
        $previousReviewedAt = $cardToReview->reviewed_at;
    
        $cardToReview->refresh();

        $this->assertNotEquals($cardToReview->reviewed_at, $previousReviewedAt);
    }
}
