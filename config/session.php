<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Gap Time Between Sessions
    |--------------------------------------------------------------------------
    |
    | When a session is completed, the user should wait a specific amount of time to start next session.
    | It's not reasonable to start the next session just after the previous session is completed.
    | In this section the amount of gap time between sessions should be specified in hours.
    |
    */

    'gap_time' => env('GAP_TIME_BETWEEN_SESSIONS', 10),
];
