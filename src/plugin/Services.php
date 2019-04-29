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
     * @return Builder|object|null
     */
    public function getBuilder()
    {
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
     * Set service components.
     */
    private function _setComponents()
    {
        $this->setComponents([
            'builder' => Builder::class,
            'schedules' => Schedules::class,
            'timers' => Timers::class,
            'logs' => Logs::class,
        ]);
    }
}