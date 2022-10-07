<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\events;

use panlatent\schedule\base\ScheduleInterface;
use yii\base\Event;

/**
 * Class ScheduleEvent
 *
 * @package panlatent\schedule\events
 * @author Panlatent <panlatent@gmail.com>
 */
class ScheduleEvent extends Event
{
    /**
     * @var ScheduleInterface
     */
    public $schedule;

    /**
     * @var bool
     */
    public $isNew = false;
}