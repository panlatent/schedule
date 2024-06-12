<?php

namespace panlatent\craft\actions\abstract;

interface TriggerInterface
{
    public function trigger(): bool;
}