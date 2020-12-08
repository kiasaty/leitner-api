<?php

namespace App\Usecases;

use App\Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SessionStarter
{
    /**
     * @var \App\Session
     */
    private $session;

    /**
     * @var int
     */
    private $breakTimeBetweenSessions;
    
    /**
     * @var int
     */
    private $maxNewCardsToBeAdded;

    /**
     * @param  \App\Session
     * @return void
     */
    public function __construct()
    {
        $this->breakTimeBetweenSessions = config('session.gap_time');

        $this->maxNewCardsToBeAdded = config('session.default_max_new_cards');
    }
    
    /**
     * Start the next session.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function start(Session $session)
    {
        $this->session = $session;
        
        $this->checkIfLastSessionIsCompleted()
            ->checkIfBreakTimeIsOver()
            ->checkIfThereAreCardsToLearn();

        DB::transaction(function () {
            $this->session->fetchNewCardsFromBox($this->maxNewCardsToBeAdded);
            $this->session->start();
        });
    }

    /**
     * Check if the session is not completed yet.
     *
     * @return $this
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    private function checkIfLastSessionIsCompleted()
    {
        if ($this->session->isRunning()) {
            abort(422, 'The current session is not completed!');
        }

        return $this;
    }

    /**
     * Check if the break time between session is over.
     *
     * @return $this
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    private function checkIfBreakTimeIsOver()
    {
        if (! $this->session->isCompleted()) {
            return $this;
        }

        $sessionEndTime = Carbon::parse($this->session->completed_at);

        if ($sessionEndTime->diffInMinutes() < $this->breakTimeBetweenSessions) {
            $diffForHumans = $sessionEndTime->addMinutes($this->breakTimeBetweenSessions)->diffForHumans(['parts' => 3]);

            abort(422, "The next session can be started in $diffForHumans.");
        }
        
        return $this;
    }

    /**
     * Check if there are any cards to learn in the session.
     *
     * @return $this
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    private function checkIfThereAreCardsToLearn()
    {
        $thereAreCardsToLearn = $this->areThereAnyCardsInSessionToLearn() || $this->areThereAnyNewCardsInBoxToLearn();

        if (! $thereAreCardsToLearn) {
            abort(422, 'There is no cards to learn!');
        }

        return $this;
    }

    /**
     * Check if there are any cards in session to learn.
     *
     * @return bool
     */
    private function areThereAnyCardsInSessionToLearn()
    {
        return (bool) $this->session->cards
            ->where('progress.level', '<>', 5)
            ->count();
    }

    /**
     * Check if there are any new cards in box to learn.
     *
     * @return bool
     */
    private function areThereAnyNewCardsInBoxToLearn()
    {
        $sessionCardsIDs = $this->session->cards->pluck('id');
            
        return $this->session->box->cards()
            ->whereNotIn('id', $sessionCardsIDs)
            ->exists();
    }
}
