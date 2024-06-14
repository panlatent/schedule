<?php

namespace panlatent\schedule\events;

use craft\events\ModelEvent;
use panlatent\craft\actions\abstract\ActionInterface;

class ActionEvent extends ModelEvent
{
    public ?ActionInterface $action = null;
}