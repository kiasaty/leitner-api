<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    /**
     * Decks of cards.
     * 
     * @var array
     */
    private $decks = ['0259', '1360', '2471', '3582', '4693', '5704', '6815', '7926', '8037', '9148'];

    /**
     * The box of the session.
     */
    public function box()
    {
        return $this->belongsTo('App\Box');
    }

    /**
     * The user of the session.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Start a new learning session.
     * 
     * @todo Refactor this.
     * @return bool
     */
    public function start()
    {
        if (! $this->areAllCardsReviewed()) {
            abort(422, 'The current session is not completed!');
        } 

        $this->checkIfBreakTimeIsOver();

        $this->addNewCards();

        $this->startNextSession();
    }

    /**
     * 
     */
    private function addNewCards()
    {
        $maxNewCards = $this->getMaxNewCards();

        $reviewingCardsIDs = $this->user->cards()
            ->where('box_id', $this->box_id)
            ->pluck('id');
            
        $cardsIDs = $this->box->cards()
            ->whereNotIn('id', $reviewingCardsIDs)
            ->take($maxNewCards)
            ->pluck('id');

        return $this->user->cards()->attach($cardsIDs);
    }

    /**
     * @todo an abilicty for the user to select the maxNewCards for each box.
     *          which overrides the default maxNewCards number.
     */
    private function getMaxNewCards()
    {
        return config('session.default_max_new_cards');
    }

    /**
     * 
     */
    private function startNextSession()
    {
        $this->number = is_null($this->started_at) || $this->number == 9 ?
            0 : 
            $this->number + 1;
            
        $this->started_at = Carbon::now();

        $this->save();
    }

    /**
     * Get the next card in the session.
     * 
     * @return \App\Card
     */
    public function getNextCard()
    {
        return $this->user->cards()
            ->where('box_id', $this->box_id)
            ->where('level', '<>', 5)
            ->where(function ($query) {
                $query->whereNull('deck')
                      ->orWhere('deck', 'like', "%{$this->number}%");
            })
            ->where(function ($query) {
                $query->whereNull('reviewed_at')
                      ->orWhere('reviewed_at', '<', $this->started_at);
            })
            ->orderBy('level')
            ->orderBy('card_id')
            ->first();
    }

    /**
     * If the user remembers the card, move it forward or turn it back to level 1 otherwise.
     * 
     * @todo if (isReviewed($card) || !isTheLastCardReviewed($card)) abort. 
     * @param \App\Card $card
     * @return bool
     */
    public function reviewCard($card, $remember)
    {
        if ($card->progress->reviewed_at > $this->started_at) {
            abort(422, 'This card has been reviewed before!');
        }

        if ($remember) {
            return $this->moveCardForwards($card);
        }

        return $this->moveCardBackwards($card);
    }

    /**
     * Determine if all cards are reviewed in the session.
     * 
     * @return bool
     */
    public function areAllCardsReviewed()
    {
        return is_null($this->getNextCard());
    }

    /**
     * Move the card one level forward.
     * 
     * @param \App\Card $card
     * @return bool
     */
    private function moveCardForwards($card)
    {
        $level = $card->progress->level;

        $data = [];

        if ($level < 5) {
            $data['level'] = $level + 1;
        }

        if ($level === 1) {
            $data['deck'] = $this->getCurrentDeck();
        }

        $data['reviewed_at'] = Carbon::now();

        return $this->user->cards()->updateExistingPivot(
            $card->id,
            $data
        );
    }

    /**
     * Turn back the card to level 1.
     * 
     * @param \App\Card $card
     * @return bool
     */
    private function moveCardBackwards($card)
    {
        $data = [
            'level'         => 1,
            'deck'          => null,
            'reviewed_at'   => Carbon::now()
        ];

        return $this->user->cards()->updateExistingPivot(
            $card->id,
            $data
        );
    }

    /**
     * Get the deck id corresponding to the current session.
     * 
     * @return string
     */
    private function getCurrentDeck()
    {
        return $this->decks[$this->number];
    }

    /**
     * Get the latest card reviewed in the session.
     * 
     * @todo if no cards has been reviewed yet in session, this might throw error.
     *          add where reviewedAt is smaller than started_at.
     * @return \App\Card
     */
    public function getLatestReviewedCard()
    {
        return $this->user->cards()
            ->where('box_id', $this->box_id)
            ->latest('reviewed_at')
            ->first();
    }

    /**
     * @todo this should be refactored.
     * @todo maybe is needed to check if the getNextCard returns not empty, then return.
     */
    private function checkIfBreakTimeIsOver()
    {
        $gapTime = config('session.gap_time');

        if ($gapTime <= 0) {
            return;
        }

        $latestReviewedCard = $this->getLatestReviewedCard();

        if (!$latestReviewedCard) {
            return;
        }
        
        $latestReviewedCardTime = new Carbon($latestReviewedCard->progress->reviewed_at);
        $diffInMin = $latestReviewedCardTime->diffInMinutes(Carbon::now());

        if ($diffInMin > $gapTime * 60) {
            return;
        }

        $hours = $gapTime - ceil($diffInMin / 60);
        $minutes = 60 - $diffInMin % 60;

        $timeLeft = $hours ? "$hours hours" : '';
        $timeLeft .= $hours && $minutes ? " and " : '';
        $timeLeft .= $minutes ? "$minutes minutes" : '';

        abort(422, "Next session can be started after $timeLeft.");
    }
}
