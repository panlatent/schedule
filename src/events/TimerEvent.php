<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
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
    public $timer;

    /**
     * @var bool
     */
    public $isNew = false;
}