<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule;

use omnilight\scheduling\Event;
use omnilight\scheduling\Schedule;
use panlatent\schedule\base\ScheduleInterface;
use panlatent\schedule\events\ScheduleBuildEvent;

/**
 * Class Builder
 *
 * @package panlatent\schedule
 * @method  Event call(callable $callback, array $parameters = [])
 * @author Panlatent <panlatent@gmail.com>
 */
class Builder extends Schedule
{
    // Constants
    // =========================================================================

    /**
     * @event ScheduleBuildEvent
     */
    const EVENT_BEFORE_BUILD = 'beforeBuild';

    /**
     * @event ScheduleBuildEvent
     */
    const EVENT_AFTER_BUILD = 'afterBuild';

    /**
     * @inheritdoc
     */
    public $cliScriptName = 'craft';

    /**
     * @param ScheduleInterface $schedule
     */
    public function schedule(ScheduleInterface $schedule): void
    {
        $schedule->build($this);
    }

    /**
     * Build schedules.
     */
    public function build(bool $force = false): static
    {
        if (!$this->beforeBuild()) {
            return $this;
        }

        $schedules = Plugin::$plugin->getSchedules();
        $schedules->force = $force;
        foreach ($schedules->getActiveSchedules() as $schedule) {
            $schedule->build($this);
        }

        $this->afterBuild();

        return $this;
    }

    /**
     * Before build.
     *
     * @return bool
     */
    public function beforeBuild(): bool
    {
        $event = new ScheduleBuildEvent([
            'builder' => $this,
            'events' => $this->_events,
        ]);

        if ($this->hasEventHandlers(static::EVENT_BEFORE_BUILD)) {
            $this->trigger(static::EVENT_BEFORE_BUILD, $event);
            $this->_events = $event->events;

            return $event->isValid;
        }

        return true;
    }

    /**
     * After build
     */
    public function afterBuild(): void
    {
        $event = new ScheduleBuildEvent([
            'builder' => $this,
            'events' => $this->_events,
        ]);

        if ($this->hasEventHandlers(static::EVENT_AFTER_BUILD)) {
            $this->trigger(static::EVENT_AFTER_BUILD, $event);
            $this->_events = $event->events;
        }
    }
}