<?php

namespace panlatent\schedule\actions;

use panlatent\craft\actions\abstract\Action;
use panlatent\craft\actions\abstract\ContextInterface;

class Closure extends Action
{
    public ?\Closure $closure = null;

    public function execute(ContextInterface $context): bool
    {
        return true;
    }

}