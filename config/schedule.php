<?php

use panlatent\schedule\builder\Schedule;

return [
    'schedules' => [
        Schedule::request('')->hourly()->onSuccess(function() {

        }),

    ],
];