<?php

namespace panlatent\schedule\builder;

use panlatent\schedule\models\Schedule;

trait Interval
{
    public function minute(): static
    {
        return $this;
    }

    public function daily(): static
    {
        return $this;
    }

    public function hourly(): static
    {
        return $this;
    }

    public function monthly(): static
    {
        return $this;
    }

    public function yearly(): static
    {
        return $this;
    }
}