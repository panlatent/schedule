<?php

namespace panlatent\schedule\builder;

trait Handler
{
    public function onSuccess(): static
    {
        return $this;
    }

    public function onFailed(): static
    {
        return $this;
    }
}