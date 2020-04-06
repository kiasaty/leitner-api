<?php

namespace App\Services;

use Carbon\Carbon;

class SessionService
{
    /**
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * @var \App\User $user
     */
    private $user;

    /**
     * @var \App\Box
     */
    private $box;

    /**
     * @var int
     */
    private $session;

    /**
     * @var \DateTime
     */
    private $sessionStartedAt;

    /**
     * @param \App\Box $box
     * @return void
     */
    public function __construct($box)
    {
        $this->request = request();

        $this->user = $this->request->user();

        $this->box = $box;

        $this->session = $this->box->subscription->session;

        $this->sessionStartedAt = $this->box->subscription->session_started_at;
    }

    /**
     * Start a new learning session.
     * 
     * @todo Refactor this.
     * @return bool
     */
    public function start()
    {
        if (! $this->areAllCardsInSessionReviewed()) {
            abort(422, 'The current session is not completed!');
        } 

        $this->checkIfBreakTimeIsOver();

        $this->attachCards();

        $this->setSession();
    }

    /**
     * 
     */
    private function attachCards()
    {
        $maxNewCards = $this->getMaxNewCards();

        $reviewingCardsIDs = $this->user->cards()
            ->where('box_id', $this->box->id)
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
    private function setSession()
    {
        $session = is_null($this->sessionStartedAt) || $this->session == 9 ?
            0 : 
            $this->session + 1;

        return $this->box->users()->updateExistingPivot($this->user->id, [
            'session'               => $session, 
            'session_started_at'    => Carbon::now()
        ]);
    }

    /**
     * Get the next card in the session.
     * 
     * @return \App\Card
     */
    public function getNextCard()
    {
        return $this->user->cards()
            ->where('box_id', $this->box->id)
            ->where('level', '<>', 5)
            ->where(function ($query) {
                $query->whereNull('deck')
                      ->orWhere('deck', 'like', "%{$this->session}%");
            })
            ->where(function ($query) {
                $query->whereNull('reviewed_at')
                      ->orWhere('reviewed_at', '<', $this->sessionStartedAt);
            })
            ->orderBy('level')
            ->orderBy('card_id')
            ->first();
    }

    /**
     * If the user remembers the card, move it forward or turn it back to level 1 otherwise.
     * 
     * @param \App\Card $card
     * @return bool
     */
    public function reviewCard($card)
    {
        if ($card->progress->reviewed_at > $this->sessionStartedAt) {
            abort(422, 'This card has been reviewed before!');
        }

        if (request('remember')) {
            return $this->moveCardForwards($card);
        }

        return $this->moveCardBackwards($card);
    }

    /**
     * Determine if all cards are reviewed in the session.
     * 
     * @return bool
     */
    public function areAllCardsInSessionReviewed()
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
            $data['deck'] = $this->getDeck();
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
     * Get the deck id corresponding to t45he current session.
     * 
     * @return string
     */
    private function getDeck()
    {
        $decks = ['0259', '1360', '2471', '3582', '4693', '5704', '6815', '7926', '8037', '9148'];

        return $decks[$this->session];
    }

    /**
     * Get the latest card reviewed in the session.
     * 
     * @return \App\Card
     */
    public function getLatestReviewedCard()
    {
        return $this->user->cards()
            ->where('box_id', $this->box->id)
            ->latest('reviewed_at')
            ->first();
    }

    /**
     * @todo this should be refactored.
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
