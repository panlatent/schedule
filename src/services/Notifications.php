<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2019 panlatent@gmail.com
 */

namespace panlatent\schedule\services;

use craft\db\Query;
use craft\helpers\Json;
use panlatent\schedule\base\NotificationInterface;
use panlatent\schedule\db\Table;
use panlatent\schedule\errors\NotificationException;
use panlatent\schedule\events\NotificationEvent;
use panlatent\schedule\events\RegisterNotificationsEvent;
use panlatent\schedule\models\ScheduleLog;
use panlatent\schedule\records\Notification;
use yii\base\Component;

/**
 * Class Notifications
 *
 * @package panlatent\schedule\services
 * @author Ryssbowh <boris@puzzlers.run>
 */
class Notifications extends Component
{
    const EVENT_REGISTER_NOTIFICATIONS = 'register-notifications';
    const EVENT_BEFORE_SAVE_NOTIFICATION = 'before-save-notification';
    const EVENT_AFTER_SAVE_NOTIFICATION = 'after-save-notification';
    const EVENT_BEFORE_DELETE_NOFITICATION = 'before-delete-notification';
    const EVENT_AFTER_DELETE_NOFITICATION = 'after-delete-notification';

    /**
     * @var array
     */
    protected $_registeredNotifications;

    /**
     * @var array
     */
    protected $_notifications;

    /**
     * Get registered notifications
     * 
     * @return array
     */
    public function getRegisteredNotifications(): array
    {
        if ($this->_registeredNotifications === null) {
            $this->registerNotifications();
        }
        return $this->_registeredNotifications;
    }

    /**
     * Get a registered notification by its handle
     * 
     * @param  string $handle
     * @return NotificationInterface
     * @throws NotificationException
     */
    public function getRegisteredNotificationByHandle(string $handle): NotificationInterface
    {
        if (isset($this->registeredNotifications[$handle])) {
            return clone $this->registeredNotifications[$handle];
        }
        throw NotificationException::noHandle($handle);
    }

    /**
     * Get all notifications
     * 
     * @return array
     */
    public function getNotifications(): array
    {
        if ($this->_notifications === null) {
            $this->_notifications = [];
            foreach (Notification::find()->orderBy('sortOrder')->all() as $record) {
                try {
                    $notification = $this->getRegisteredNotificationByHandle($record->handle);
                    $attributes = array_intersect_key($record->getAttributes(), $notification->getAttributes());
                    $notification->setAttributes($attributes, false);
                    $this->_notifications[] = $notification;
                } catch (NotificationException $e) {
                    \Craft::$app->errorHandler->logException($e);
                }
            }
        }
        return $this->_notifications;
    }

    /**
     * Get all notifications for a schedule id
     * 
     * @param  int    $id
     * @return array
     */
    public function getNotificationsByScheduleId(int $id): array
    {
        return array_filter($this->notifications, function ($notification) use ($id) {
            return ($notification->scheduleId == $id);
        });
    }

    /**
     * Get a notification by id
     * 
     * @param  int    $id
     * @return ?NotificationInterface
     */
    public function getNotificationById(int $id): ?NotificationInterface
    {
        foreach ($this->notifications as $notification) {
            if ($notification->id == $id) {
                return $notification;
            }
        }
        return null;
    }

    /**
     * Save a notification
     * 
     * @param NotificationInterface $notification
     * @param bool $runValidation
     * @return bool
     */
    public function saveNotification(NotificationInterface $notification, bool $runValidation = true): bool
    {
        $isNew = !$notification->id;

        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_NOTIFICATION)) {
            $this->trigger(self::EVENT_BEFORE_SAVE_NOTIFICATION, new NotificationEvent([
                'notification' => $notification,
                'isNew' => $isNew,
            ]));
        }

        if (!$notification->beforeSave($isNew)) {
            return false;
        }

        if ($runValidation && !$notification->validate()) {
            \Craft::info("Timer not saved due to validation error.", __METHOD__);
            return false;
        }

        $transaction = \Craft::$app->getDb()->beginTransaction();
        try {
            if (!$isNew) {
                $record = Notification::findOne(['id' => $notification->id]);
                if (!$record) {
                    throw NotificationException::noId($notification->id);
                }
            } else {
                $record = new Notification();
                $record->scheduleId = $notification->scheduleId;
            }

            $record->handle = $notification->handle;
            $record->enabled = $notification->enabled;
            $record->settings = Json::encode($notification->settings);

            if ($isNew) {
                $lastSortOrder = (new Query())
                    ->select('sortOrder')
                    ->from(Table::SCHEDULENOTIFICATIONS)
                    ->where([
                        'scheduleId' => $notification->scheduleId,
                    ])
                    ->orderBy('sortOrder')
                    ->scalar();

                $record->sortOrder = (int)$lastSortOrder + 1;
            }

            $record->save(false);

            if ($isNew) {
                $notification->id = $record->id;
            }

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        if ($isNew) {
            $this->_notifications[] = $notification;
        }

        $notification->afterSave($isNew);

        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_NOTIFICATION)) {
            $this->trigger(self::EVENT_AFTER_SAVE_NOTIFICATION, new NotificationEvent([
                'notification' => $notification,
                'isNew' => $isNew,
            ]));
        }

        return true;
    }

    /**
     * Delete a notification
     * 
     * @param NotificationInterface $notification
     * @return bool
     */
    public function deleteNotification(NotificationInterface $notification): bool
    {
        if ($this->hasEventHandlers(self::EVENT_BEFORE_DELETE_NOFITICATION)) {
            $this->trigger(self::EVENT_BEFORE_DELETE_TIMER, new NotificationEvent([
                'notification' => $notification,
            ]));
        }

        $db = \Craft::$app->getDb();

        $transaction = $db->beginTransaction();
        try {
            $db->createCommand()
                ->delete(Table::SCHEDULENOTIFICATIONS, [
                    'id' => $notification->id,
                ])
                ->execute();

            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE_NOFITICATION)) {
            $this->trigger(self::EVENT_AFTER_DELETE_NOFITICATION, new NotificationEvent([
                'notification' => $notification,
            ]));
        }

        return true;
    }

    /**
     * Reorder notifications
     * 
     * @param array $notificationIds
     * @return bool
     */
    public function reorderNotifications(array $notificationIds): bool
    {
        $db = \Craft::$app->getDb();

        $transaction = $db->beginTransaction();
        try {
            foreach ($notificationIds as $order => $id) {
                $db->createCommand()
                    ->update(Table::SCHEDULENOTIFICATIONS, [
                        'sortOrder' => $order + 1
                    ], [
                        'id' => $id,
                    ])
                    ->execute();
            }

            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        return true;
    }

    /**
     * Register notifications through an event
     */
    protected function registerNotifications()
    {
        $event = new RegisterNotificationsEvent();
        $this->trigger(self::EVENT_REGISTER_NOTIFICATIONS, $event);
        $this->_registeredNotifications = $event->notifications;
    }
}