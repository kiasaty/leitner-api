<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Gap Time Between Sessions
    |--------------------------------------------------------------------------
    |
    | When a session is completed, the user should wait a specific amount of time to start next session.
    | It's not reasonable to start the next session just after the previous session is completed.
    | In this section the amount of gap time between sessions should be specified in minutes.
    |
    */

    'gap_time' => 600,

    /*
    |--------------------------------------------------------------------------
    | Default Max New Cards
    |--------------------------------------------------------------------------
    |
    | When a new session is started, a specific number of new cards would be added to the learning process.
    | Each user can select a max new cards number to be added for each box that he/she is studying.
    | But if the user has not specified a max new cards number, this default number is used.
    |
    */

    'default_max_new_cards' => 10,
];
