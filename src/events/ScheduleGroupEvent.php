<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\events;

use panlatent\schedule\models\ScheduleGroup;
use yii\base\Event;

/**
 * Class ScheduleGroupEvent
 *
 * @package panlatent\schedule\events
 * @author Panlatent <panlatent@gmail.com>
 */
class ScheduleGroupEvent extends Event
{
    /**
     * @var ScheduleGroup|null
     */
    public $group;

    /**
     * @var bool
     */
    public $isNew = false;
}