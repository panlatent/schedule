<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\plugin;

use panlatent\schedule\Builder;
use panlatent\schedule\services\Logs;
use panlatent\schedule\services\Notifications;
use panlatent\schedule\services\Schedules;
use panlatent\schedule\services\Timers;

/**
 * Trait Services
 *
 * @package panlatent\schedule\plugin
 * @property-read Builder $builder
 * @property-read Schedules $schedules
 * @property-read Logs $logs
 * @author Panlatent <panlatent@gmail.com>
 */
trait Services
{
    /**
     * @since 0.3.2
     * @return Builder
     */
    public function createBuilder(): Builder
    {
        return new Builder();
    }

    /**
     * @deprecated
     * @see createBuilder()
     * @return Builder|object|null
     */
    public function getBuilder()
    {
        \Craft::$app->getDeprecator()->log('schedule.getBuilder()', 'This method has been deprecated, singleton objects will have bad problems in persistent mode.');
        return $this->get('builder');
    }

    /**
     * @return Schedules
     */
    public function getSchedules(): Schedules
    {
        return $this->get('schedules');
    }

    /**
     * @return Timers
     */
    public function getTimers(): Timers
    {
        return $this->get('timers');
    }

    /**
     * @return Logs
     */
    public function getLogs(): Logs
    {
        return $this->get('logs');
    }

    /**
     * @return Notifications
     */
    public function getNotifications(): Notifications
    {
        return $this->get('notifications');
    }

    /**
     * Set service components.
     */
    private function _setComponents()
    {
        $this->setComponents([
            'builder' => Builder::class,
            'schedules' => Schedules::class,
            'timers' => Timers::class,
            'logs' => Logs::class,
            'notifications' => Notifications::class,
        ]);
    }
}