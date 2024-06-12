<?php

namespace panlatent\schedule\builder;

use panlatent\schedule\models\Schedule;

trait Interval
{
    public function hourly(): static
    {
        return $this;
    }
}