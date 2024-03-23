<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */



namespace panlatent\schedule\events;

use panlatent\schedule\base\TimerInterface;
use yii\base\Event;

/**
 * Class TimerEvent
 *
 * @package panlatent\schedule\events
 * @author Panlatent <panlatent@gmail.com>
 */
class TimerEvent extends Event
{
    /**
     * @var TimerInterface|null
     */
    public ?TimerInterface $timer = null;

    /**
     * @var bool
     */
    public bool $isNew = false;
}