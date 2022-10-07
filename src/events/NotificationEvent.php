<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\events;

use panlatent\schedule\base\NotificationInterface;
use yii\base\Event;

/**
 * Class NotificationEvent
 *
 * @package panlatent\schedule\events
 * @author Ryssbowh <boris@puzzlers.run>
 */
class NotificationEvent extends Event
{
    /**
     * @var NotificationInterface|null
     */
    public $notification;

    /**
     * @var bool
     */
    public $isNew = false;
}