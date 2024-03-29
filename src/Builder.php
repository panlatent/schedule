<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule;

use Closure;
use Craft;
use Crunz\Event;
use Crunz\Schedule as Scheduler;
use DateTimeZone;
use panlatent\schedule\base\ScheduleInterface;
use panlatent\schedule\events\ScheduleBuildEvent;
use yii\base\Component;

/**
 * Class Builder
 *
 * @package panlatent\schedule
 * @author Panlatent <panlatent@gmail.com>
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
     * @param ScheduleInterface $schedule
     * @deprecated
     */
    public function schedule(ScheduleInterface $schedule): void
    {
        $schedule->build($this);
    }

    /**
     * @deprecated
     * @see Builder::closure()
     */
    public function call(callable $callback, array $parameters = []): BuilderEvent
    {
        if (!$callback instanceof Closure) {
            $callback = Closure::fromCallable($callback);
        }
        return $this->closure($callback);
    }

    /**
     * @param Closure $callback
     * @return BuilderEvent
     */
    public function closure(Closure $callback): BuilderEvent
    {
        $event = new BuilderEvent($callback);
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
     * @return Event[]
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