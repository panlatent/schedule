<?php

use panlatent\schedule\builder\Schedule;

return [
    'schedules' => [
        Schedule::closure(static fn() => true)->minute(),

        Schedule::request('')->hourly()->onSuccess(function() {

        }),

        Schedule::exec('ls')->hourly()->onSuccess(function() {

        }),

        Schedule::console('db/backup')->hourly()->onSuccess(function() {

        }),
    ],

];