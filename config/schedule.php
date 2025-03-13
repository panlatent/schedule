<?php

use panlatent\schedule\models\Schedule;

return [
    'actions' => [
        \panlatent\schedule\base\Action::make()


    ],

    'schedules' => [
        Schedule::make()
            ->hourly(),

        Schedule::make()
            ->closure(static fn() => true)
            ->minute(),

        Schedule::make()
            ->request('')
            ->hourly()
            ->onSuccess(function() {

            }),

        Schedule::make()
            ->exec('ls')
            ->hourly()
            ->onSuccess(function() {

            }),

        Schedule::make()
            ->console('db/backup')
            ->hourly()
            ->onSuccess(function() {

            }),
    ],
];