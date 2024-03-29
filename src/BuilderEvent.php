<?php

namespace panlatent\schedule;

use Closure;
use Cron\CronExpression;
use panlatent\schedule\base\TimerInterface;

class BuilderEvent
{
    // Properties
    // =========================================================================

    /**
     * @var TimerInterface|null
     */
    public ?TimerInterface $timer = null;

    /**
     * @var string Cron expression
     */
    public string $expression = '';

    /**
     * @var \DateTimeZone|null
     */
    public ?\DateTimeZone $timezone = null;

    /**
     * @var Closure
     */
    protected Closure $callback;

    // Public Methods
    // =========================================================================

    /**
     * @param Closure $callback
     */
    public function __construct(Closure $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @return string
     */
    public function getSummary(): string
    {
        if ($this->timer === null) {
            return '';
        }
        $schedule = $this->timer->getSchedule();
        return sprintf('%d#%s[%s] %d#%s', $schedule->id, $schedule->name, $schedule->handle, $this->timer->id, $this->timer->uid);
    }

    /**
     * @deprecated
     * @see BuilderEvent::getSummary()
     */
    public function getSummaryForDisplay(): string
    {
        return $this->getSummary();
    }

    /**
     * @param string $expression
     * @return $this
     */
    public function expression(string $expression): static
    {
        $this->expression = $expression;
        return $this;
    }

    /**
     * @param TimerInterface $timer
     * @return $this
     */
    public function timer(TimerInterface $timer): static
    {
        $this->timer = $timer;
        return $this;
    }

    /**
     * @deprecated Use expression()
     * @see BuilderEvent::expression()
     */
    public function cron(string $expression): static
    {
        $this->expression = $expression;
        return $this;
    }

    public function run(): void
    {
        call_user_func($this->callback);
    }

    public function isDue($app): bool
    {
        $date = new \DateTime('now');
        if ($this->timezone) {
            $date->setTimezone($this->timezone);
        }
        return (new CronExpression($this->expression))->isDue($date);
    }
}