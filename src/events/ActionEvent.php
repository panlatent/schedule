<?php

namespace panlatent\schedule\events;

use craft\events\ModelEvent;
use panlatent\craft\actions\abstract\SavableActionInterface;

class ActionEvent extends ModelEvent
{
    public ?SavableActionInterface $action = null;
}