<?php

namespace App\Usecases;

class CardReviewer
{
    /**
     * The session that the reviewing card is in it.
     *
     * @var \App\Session
     */
    private $session;

    /**
     * The card that should be reviewed.
     *
     * @var \App\Card
     */
    private $card;
    
    /**
     * Indicates if the user has remembered the card.
     *
     * @var bool
     */
    private $remember;

    public function __construct($session, $card, $remember)
    {
        $this->session = $session;
        $this->card = $card;
        $this->remember = $remember;
    }
    
    public function review()
    {
        $this->checkIfSessionIsRunning()
            ->checkIfCardIsInSession()
            ->checkIfCardHasBeenReviewed();

        if ($this->remember) {
            $this->session->promoteCard($this->card);
        } else {
            $this->session->demoteCard($this->card);
        }
    }

    /**
     * Check if the session is in "running" state.
     *
     * @return $this
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    private function checkIfSessionIsRunning()
    {
        if (! $this->session->isRunning()) {
            abort(422, 'The session is weather completed or not started!');
        }

        return $this;
    }

    /**
     * Check if the the card is associated with the session.
     *
     * @return $this
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    private function checkIfCardIsInSession()
    {
        if (! $this->session->hasCard($this->card)) {
            abort(422, 'The card is not present in the session!');
        }

        return $this;
    }
    
    /**
     * Check if the card has been reviewed before.
     *
     * @return $this
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function checkIfCardHasBeenReviewed()
    {
        if ($this->session->isCardReviewed($this->card)) {
            abort(422, 'This card has been reviewed before!');
        }

        return $this;
    }
}
