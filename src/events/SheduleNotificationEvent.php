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
 * Class SheduleNotificationEvent
 *
 * @package panlatent\schedule\events
 * @author Ryssbowh <boris@puzzlers.run>
 */
class SheduleNotificationEvent extends Event
{
    /**
     * @var ScheduleInterface
     */
    public $schedule;

    /**
     * @var bool
     */
    public $isValid = true;
}