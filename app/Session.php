<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    
    /**
     * Decks of cards.
     * 
     * @var array
     */
    public const DECKS = [
        1   => 'Current',
        2   => '0-2-5-9',
        3   => '1-3-6-0',
        4   => '2-4-7-1',
        5   => '3-5-8-2',
        6   => '4-6-9-3',
        7   => '5-7-0-4',
        8   => '6-8-1-5',
        9   => '7-9-2-6',
        10  => '8-0-3-7',
        11  => '9-1-4-8',
        12  => 'Retired'
    ];

    /**
     * 
     */
    private function getDecks()
    {
        return preg_grep("/(Current|{$this->number})/i", self::DECKS);
    }

    /**
     * Get the user's first name.
     *
     * @param  string  $value
     * @return string
     */
    public function getDecksIdsAttribute($value)
    {
        return array_keys($this->getDecks());
    }

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
        if ($this->started_at && !$this->ended_at) {
            abort(422, 'The current session is not completed!');
        } 

        $this->checkIfBreakTimeIsOver();

        if (!$this->isThereAnyCardsToLearn()) {
            abort(422, 'There is no cards to learn!');
        }

        $this->addNewCards();

        $this->startNextSession();
    }

    /**
     * 
     */
    public function end()
    {
        $this->update([
            'ended_at' => Carbon::now()
        ]);
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
     * 
     */
    private function isThereAnyCardsToLearn()
    {
        $isThereAnyCardInSessionToLearn = $this->user->cards()
            ->where('box_id', $this->box_id)
            ->where('level', '<>', 5)
            ->exists();

        if ($isThereAnyCardInSessionToLearn) {
            return true;
        }

        $sessionCardsIDs = $this->user->cards()
            ->where('box_id', $this->box_id)
            ->pluck('id');
            
        return $this->box->cards()
            ->whereNotIn('id', $sessionCardsIDs)
            ->exists();
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
        $this->update([
            'number' => is_null($this->started_at) || $this->number == 9 ? 0 : $this->number + 1,  
            'started_at' => Carbon::now(),
            'ended_at' => null
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
            ->where('box_id', $this->box_id)
            ->whereIn('deck_id', $this->decks_ids)
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
     * @param \App\Card $card
     * @return bool
     */
    public function review($card, $remember)
    {
        if ($this->isReviewed($card)) {
            abort(422, 'This card has been reviewed before!');
        }

        if ($remember) {
            return $this->promoteCard($card);
        }

        return $this->demoteCard($card);
    }
    
    /**
     * Check if the card is reviewed.
     * 
     * @param \App\Card $card
     * @return bool
     */
    public function isReviewed($card)
    {
        return $card->progress->reviewed_at > $this->started_at;
    }

    /**
     * Move the card one level forward.
     * 
     * @param \App\Card $card
     * @return bool
     */
    private function promoteCard($card)
    {
        $level = $card->progress->level;

        $data = [];

        if ($level < 5) {
            $data['level'] = $level + 1;
        }

        if ($level === 1) {
            $data['deck_id'] = $this->getCurrentDeck();
        } else if ($level === 4) {
            $data['deck_id'] = 12;
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
    private function demoteCard($card)
    {
        $data = [
            'level'         => 1,
            'deck_id'       => 1,
            'difficulty'    => $card->progress->difficulty + 1,
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
        return array_keys(preg_grep("/^{$this->number}/i", self::DECKS))[0];
    }

    /**
     * 
     */
    private function checkIfBreakTimeIsOver()
    {
        $sessionEndTime = $this->ended_at;

        if (!$sessionEndTime) {
            return;
        }

        $sessionEndTime = Carbon::parse($sessionEndTime);
        
        $gapTimeBetweenSessions = config('session.gap_time');

        if ($sessionEndTime->diffInMinutes() > $gapTimeBetweenSessions) {
            return;
        }

        $diffForHumans = $sessionEndTime->addMinutes($gapTimeBetweenSessions)->diffForHumans(['parts' => 3]);

        abort(422, "The next session can be started in $diffForHumans.");
    }
}
