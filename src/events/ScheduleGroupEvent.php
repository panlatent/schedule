<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
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