<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\events;

use craft\events\ModelEvent;
use panlatent\schedule\models\ScheduleGroup;

/**
 * Class ScheduleGroupEvent
 *
 * @package panlatent\schedule\events
 * @author Panlatent <panlatent@gmail.com>
 */
class ScheduleGroupEvent extends ModelEvent
{
    /**
     * @var ScheduleGroup|null
     */
    public ?ScheduleGroup $group = null;
}