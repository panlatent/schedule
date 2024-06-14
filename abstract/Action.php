<?php

namespace panlatent\craft\actions\abstract;

use craft\base\SavableComponent;

abstract class Action extends SavableComponent implements ActionInterface
{
    public ?string $uid = null;
}