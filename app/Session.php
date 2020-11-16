<?php

namespace App;

use App\Usecases\CardReviewer;
use App\Usecases\SessionStarter;
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
     * Get the deck id corresponding to the current session.
     *
     * @return string
     */
    private function getCurrentDeck()
    {
        return array_keys(preg_grep("/^{$this->number}/i", self::DECKS))[0];
    }

    /**
     * Get the decks_ids.
     *
     * @return string
     */
    public function getDecksIdsAttribute()
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
     * The cards associated with the session.
     */
    public function cards()
    {
        return $this->belongsToMany('App\Card', 'card_user')
            ->as('progress')
            ->withPivot(['user_id', 'level', 'deck_id', 'difficulty', 'reviewed_at']);
    }

    /**
     * Start a new learning session.
     *
     * @todo Refactor this.
     * @return bool
     */
    public function start()
    {
        return (new SessionStarter($this))->start();
    }

    /**
     *
     */
    public function end()
    {
        $this->update([
            'ended_at' => $this->freshTimestamp()
        ]);
    }

    /**
     * Get the next card in the session.
     *
     * @return \App\Card
     */
    public function getNextCard()
    {
        return $this->cards()
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
     * Review a card in the session.
     *
     * @param  int $cardID
     * @param  int  $remember
     * @return void
     */
    public function review($cardID, $remember)
    {
        $card = $this->getCard($cardID);

        (new CardReviewer($this, $card, $remember))->review();
    }
    
    /**
     * Check if the card has been reviewed.
     *
     * @param \App\Card $card
     * @return bool
     */
    public function isCardReviewed($card)
    {
        return $card->progress->reviewed_at > $this->started_at;
    }

    /**
     * Move the card one level forward.
     *
     * @param \App\Card $card
     * @return void
     */
    public function promoteCard($card)
    {
        $level = $card->progress->level;

        $data = [];

        if ($level < 5) {
            $data['level'] = $level + 1;
        }

        if ($level === 1) {
            $data['deck_id'] = $this->getCurrentDeck();
        } elseif ($level === 4) {
            $data['deck_id'] = 12;
        }

        $data['reviewed_at'] = $this->freshTimestamp();

        $this->updateCard($card->id, $data);
    }

    /**
     * Turn back the card to level 1.
     *
     * @param \App\Card $card
     * @return void
     */
    public function demoteCard($card)
    {
        $this->updateCard($card->id, [
            'level'         => 1,
            'deck_id'       => 1,
            'difficulty'    => $card->progress->difficulty + 1,
            'reviewed_at'   => $this->freshTimestamp()
        ]);
    }

    /**
     * Check if the session is started.
     *
     * @return bool
     */
    public function isStarted()
    {
        return (bool) $this->started_at;
    }

    /**
     * Check if the session is completed.
     *
     * @return bool
     */
    public function isCompleted()
    {
        return (bool) $this->ended_at;
    }

    /**
     * Check if the session is running.
     *
     * @return bool
     */
    public function isRunning()
    {
        return $this->isStarted() && !$this->isCompleted();
    }

    /**
     * Get a card that is present in the session.
     *
     * @param  int  $cardID
     * @return \App\Card
     */
    public function getCard($cardID)
    {
        return $this->relationLoaded('cards') ?
            $this->cards->find($cardID) :
            $this->cards()->find($cardID);
    }

    /**
     * Check if a card is present in the session.
     *
     * @param  \App\Card|\ArrayAccess|array|int  $card
     * @return bool
     */
    public function hasCard($card)
    {
        $cardID = is_int($card) ? $card : $card['id'];

        return $this->relationLoaded('cards') ?
            $this->cards->where('id', $cardID)->exists() :
            $this->cards()->where('id', $cardID)->exists();
    }

    /**
     * Update the card progress information in the session.
     *
     * @param  int  $cardID
     * @param  array  $attributes
     * @return void
     */
    public function updateCard($cardID, $attributes)
    {
        $this->cards()->updateExistingPivot($cardID, $attributes);
    }
}
