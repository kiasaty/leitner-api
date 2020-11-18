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
    public function __construct(Session $session)
    {
        $this->session = $session;

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
    public function start()
    {
        $this->checkIfLastSessionIsCompleted()
            ->checkIfBreakTimeIsOver()
            ->checkIfThereAreCardsToLearn();

        DB::transaction(function () {
            $this->addNewCardsToSession()
                ->startNextSession();
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
     * @todo make sure ended_at is not carbon instance.
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
        
        $sessionEndTime = Carbon::parse($this->session->ended_at);

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
            ->where('level', '<>', 5)
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

    /**
     * Add new cards to the session.
     *
     * @return $this
     */
    private function addNewCardsToSession()
    {
        $reviewingCardsIDs = $this->session->cards->pluck('id');
            
        $cardsIDs = $this->session->box->cards()
            ->whereNotIn('id', $reviewingCardsIDs)
            ->take($this->maxNewCardsToBeAdded)
            ->pluck('id');

        $this->session->cards()->attach($cardsIDs);

        return $this;
    }

    /**
     * Start next session.
     *
     * @return $this
     */
    private function startNextSession()
    {
        $this->session->update([
            'number'        => $this->getNextSessionNumber(),
            'started_at'    => Carbon::now(),
            'ended_at'      => null
        ]);

        return $this;
    }

    /**
     * Get the next session number.
     *
     * @return int
     */
    private function getNextSessionNumber()
    {
        return is_null($this->session->started_at) || $this->session->number == 9 ?
            0 :
            $this->session->number + 1;
    }
}
