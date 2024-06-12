<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule;

use Craft;
use craft\errors\DeprecationException;
use panlatent\schedule\base\ScheduleInterface;
use panlatent\schedule\base\TimerInterface;
use panlatent\schedule\events\ScheduleBuildEvent;
use yii\base\Component;

/**
 * Class Builder
 *
 * @package panlatent\schedule
 * @author Panlatent <panlatent@gmail.com>
 * @deprecated since 1.0.0
 */
class Builder extends Component
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
     * @var string
     */
    public string $cliScriptName = 'craft';

    // Properties
    // =========================================================================

    /**
     * @var BuilderEvent[]
     */
    private array $_events = [];

    // Public Methods
    // =========================================================================

    /**
     * @deprecated
     */
    public function call(callable $callback, array $parameters = []): BuilderEvent
    {
        throw new DeprecationException();
    }

    /**
     * @param ScheduleInterface $schedule
     * @param TimerInterface $timer
     * @return BuilderEvent
     */
    public function resolve(ScheduleInterface $schedule, TimerInterface $timer): BuilderEvent
    {
        $event = new BuilderEvent($schedule, $timer);
        $this->_events[] = $event;
        return $event;
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
     * @return BuilderEvent[]
     */
    public function dueEvents($app = null): array
    {
        if ($app === null) {
            $app = Craft::$app;
        }

        return array_filter($this->_events, function(BuilderEvent $event) use ($app)
        {
            return $event->isDue($app);
        });
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