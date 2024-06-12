<?php

namespace panlatent\craft\actions\abstract;

use craft\base\SavableComponentInterface;

interface ActionInterface extends SavableComponentInterface
{
    public function execute(ContextInterface $context): bool;
}