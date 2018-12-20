<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
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