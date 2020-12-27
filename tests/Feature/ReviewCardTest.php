<?php

namespace Tests\Feature;

use App\Box;
use App\Card;
use Carbon\Carbon;
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
    public function card_can_not_be_reviewed_if_user_has_no_session_on_the_given_box()
    {
        $box = Box::factory()->hasCards(1)->create();
        $session = $box->createSession($box->creator);
        $session->addCards($box->cards->pluck('id'));

        $cardToReview = $session->cards()->first();

        $this->loginUser();

        $this->post("boxes/{$box->id}/session/cards/{$cardToReview->id}/review", ['remember' => true])
            ->seeStatusCode(404);
    }
    
    /** @test */
    public function card_can_not_be_reviewed_if_card_does_not_exist_in_box()
    {
        $box = Box::factory()->hasCards(1)->create();
        $session = $box->createSession($box->creator);
        $session->addCards($box->cards->pluck('id'));

        $card = Card::factory()->create();

        $this->loginUser($box->creator);

        $this->post("boxes/{$box->id}/session/cards/{$card->id}/review", ['remember' => true])
            ->seeStatusCode(404);
    }

    /** @test */
    public function card_can_not_be_reviewed_if_card_does_not_belong_to_the_given_box()
    {
        $box = Box::factory()->hasCards(1)->create();
        $session = $box->createSession($box->creator);
        $session->addCards($box->cards->pluck('id'));

        $anotherBox = Box::factory()->hasCards(1)->create();

        $this->loginUser($box->creator);
        
        $this->post("boxes/{$box->id}/session/cards/{$anotherBox->cards->first()->id}/review", ['remember' => true])
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
    
    /** @test */
    public function card_can_not_be_reviewed_if_card_is_retired()
    {
        $box = Box::factory()->hasCards(1)->create();

        $session = $box->createSession($box->creator);

        $session->start();

        // here you should travel in time to the future.
        
        $session->addCards(
            $box->cards->pluck('id'),
            ['level' => 5, 'deck_id' => 12, 'reviewed_at' => Carbon::now()->subMinute()]
        );

        $cardToReview = $session->cards->first();
        
        $this->loginUser($box->creator);

        $this->post("boxes/{$box->id}/session/cards/{$cardToReview->id}/review", ['remember' => true])
            ->seeStatusCode(422);

        $previousReviewedAt = $cardToReview->progress->reviewed_at;
        $cardToReview->progress->refresh();
            
        $this->assertEquals($cardToReview->progress->level, 5);
        $this->assertEquals($cardToReview->progress->deck_id, 12);
        $this->assertEquals($previousReviewedAt, $cardToReview->progress->reviewed_at);
        
    }
    
    /** @test */
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

    /** @test */
    public function card_can_not_be_reviewed_if_it_has_been_reviewed_in_current_session()
    {
        $box = Box::factory()->hasCards(3)->create();

        $session = $box->createSession($box->creator_id);

        $session->addCards(
            $box->cards->pluck('id')
        );

        $session->start();

        // go a little forward in time.

        $cardToReview = $session->getNextCard();
        
        $session->promoteCard($cardToReview);

        $this->loginUser($box->creator);

        $this->post("boxes/{$box->id}/session/cards/{$cardToReview->id}/review", ['remember' => true])
            ->seeStatusCode(422);

        $cardToReview = $session->getNextCard();
        
        $session->demoteCard($cardToReview);
            
        $this->post("boxes/{$box->id}/session/cards/{$cardToReview->id}/review", ['remember' => true])
            ->seeStatusCode(422);
    }

    /** @test */
    public function a_card_will_be_promoted_if_user_remembers_it()
    {
        $box = Box::factory()->hasCards(1)->create();

        $session = $box->createSession($box->creator_id);

        $session->addCards(
            $box->cards->pluck('id')
        );

        $session->start();

        $cardToReview = $session->getNextCard();

        $this->loginUser($box->creator);
        
        $this->post("boxes/{$box->id}/session/cards/{$cardToReview->id}/review", ['remember' => true])
            ->seeStatusCode(200);
        
        $previousLevel = $cardToReview->progress->level;
        $cardToReview->progress->refresh();
        $newLevel = $cardToReview->progress->level;

        $this->assertEquals($newLevel, $previousLevel + 1);
    }

    /** @test */
    public function a_card_will_be_demoted_if_user_does_not_remember_it()
    {
        $box = Box::factory()->hasCards(1)->create();

        $session = $box->createSession($box->creator_id);

        $session->addCards(
            $box->cards->pluck('id'),
            ['level' => 3, 'deck_id' => 2]
        );

        $session->start();

        $cardToReview = $session->getNextCard();

        $this->loginUser($box->creator);

        $this->post("boxes/{$box->id}/session/cards/{$cardToReview->id}/review", ['remember' => false])
            ->seeStatusCode(200);
        
        $cardToReview->progress->refresh();
        
        $this->assertEquals($cardToReview->progress->level, 1);
        $this->assertEquals($cardToReview->progress->deck_id, 1);
    }

    /** @test */
    public function when_user_does_not_remember_a_card_the_difficulty_of_card_increases()
    {
        $box = Box::factory()->hasCards(1)->create();

        $session = $box->createSession($box->creator_id);

        $session->addCards(
            $box->cards->pluck('id'),
            ['level' => 3, 'deck_id' => 2]
        );

        $session->start();

        $cardToReview = $session->getNextCard();

        $this->loginUser($box->creator);

        $this->post("boxes/{$box->id}/session/cards/{$cardToReview->id}/review", ['remember' => false])
            ->seeStatusCode(200);

        $previousDifficulty = $cardToReview->progress->difficulty;
        $cardToReview->progress->refresh();
        
        $this->assertEquals($cardToReview->progress->difficulty, $previousDifficulty + 1);
    }

    /** @test */
    public function when_user_remembers_a_card_the_difficulty_of_card_does_not_change()
    {
        $box = Box::factory()->hasCards(1)->create();

        $session = $box->createSession($box->creator_id);

        $session->addCards(
            $box->cards->pluck('id'),
            ['level' => 3, 'deck_id' => 2]
        );

        $session->start();

        $cardToReview = $session->getNextCard();

        $this->loginUser($box->creator);

        $this->post("boxes/{$box->id}/session/cards/{$cardToReview->id}/review", ['remember' => true])
            ->seeStatusCode(200);

        $previousDifficulty = $cardToReview->progress->difficulty;
        $cardToReview->progress->refresh();
        
        $this->assertEquals($cardToReview->progress->difficulty, $previousDifficulty);
    }

    /** @test */
    public function when_a_card_gets_promoted_for_the_first_time_it_gets_the_current_session_deck()
    {
        $box = Box::factory()->hasCards(1)->create();

        $session = $box->createSession($box->creator_id);

        $session->addCards(
            $box->cards->pluck('id')
        );

        $session->start();

        $cardToReview = $session->getNextCard();

        $this->loginUser($box->creator);

        $this->post("boxes/{$box->id}/session/cards/{$cardToReview->id}/review", ['remember' => true])
            ->seeStatusCode(200);
        
        $cardToReview->progress->refresh();

        $this->assertEquals($cardToReview->progress->deck_id, $session->deck_id);
    }

    /** 
     * @todo check if this should be a unit test.
     * 
     * @test 
     */
    public function when_a_card_is_reviewed_the_reviewed_at_column_is_updated()
    {
        $box = Box::factory()->hasCards(1)->create();

        $session = $box->createSession($box->creator_id);

        $session->addCards(
            $box->cards->pluck('id')
        );

        $session->start();

        $cardToReview = $session->getNextCard();

        $this->loginUser($box->creator);

        $this->post("boxes/{$box->id}/session/cards/{$cardToReview->id}/review", ['remember' => true])
            ->seeStatusCode(200);
        
        $previousReviewedAt = $cardToReview->progress->reviewed_at;
    
        $cardToReview->progress->refresh();

        $this->assertNotEquals($cardToReview->progress->reviewed_at, $previousReviewedAt);
    }
}
