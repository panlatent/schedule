<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\events;

use panlatent\schedule\base\NotificationInterface;
use panlatent\schedule\notifications\Email;
use panlatent\schedule\notifications\Slack;
use yii\base\Event;

/**
 * Class RegisterNotificationsEvent
 *
 * @package panlatent\schedule\events
 * @author Ryssbowh <boris@puzzlers.run>
 */
class RegisterNotificationsEvent extends Event
{   
    /**
     * @var NotificationInterface[]
     */
    protected $_notifications = [];

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        $this->register(new Email);
        $this->register(new Slack);
    }

    /**
     * Registers a new type of notification
     * 
     * @param NotificationInterface $notification
     */
    public function register(NotificationInterface $notification)
    {
        $this->_notifications[$notification->handle] = $notification;
    }

    /**
     * Get registered notifications
     * 
     * @return array
     */
    public function getNotifications(): array
    {
        return $this->_notifications;
    }
}