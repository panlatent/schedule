<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\events;

use yii\base\Event;

class ScheduleBuildEvent extends Event
{
    public $builder;

    public $events;
}