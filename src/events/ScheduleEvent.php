<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\events;

use craft\events\ModelEvent;
use panlatent\schedule\models\Schedule;

/**
 * Class ScheduleEvent
 *
 * @package panlatent\schedule\events
 * @author Panlatent <panlatent@gmail.com>
 */
class ScheduleEvent extends ModelEvent
{
    public ?Schedule $schedule = null;
}