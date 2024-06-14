<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */



namespace panlatent\schedule\events;

use craft\events\ModelEvent;
use panlatent\schedule\base\TimerInterface;

/**
 * Class TimerEvent
 *
 * @package panlatent\schedule\events
 * @author Panlatent <panlatent@gmail.com>
 */
class TimerEvent extends ModelEvent
{
    /**
     * @var TimerInterface|null
     */
    public ?TimerInterface $timer = null;
}