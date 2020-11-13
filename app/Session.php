<?php

namespace App;

use App\Usecases\SessionStarter;
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
            'ended_at' => Carbon::now()
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
        } elseif ($level === 4) {
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
}
