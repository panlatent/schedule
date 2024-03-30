<?php

namespace panlatent\schedule;

use Closure;
use Cron\CronExpression;
use panlatent\schedule\base\Schedule;
use panlatent\schedule\base\ScheduleInterface;
use panlatent\schedule\base\TimerInterface;
use yii\base\Component;

/**
 * @property-read ScheduleInterface $schedule
 */
class BuilderEvent extends Component
{
    // Properties
    // =========================================================================

    /**
     * @var \DateTimeZone|null
     */
    public ?\DateTimeZone $timezone = null;

    /**
     * @var ScheduleInterface
     */
    private ScheduleInterface $_schedule;

    /**
     * @var TimerInterface
     */
    private TimerInterface $_timer;

    // Public Methods
    // =========================================================================

    public function __construct(ScheduleInterface $schedule, TimerInterface $timer, $config = [])
    {
        $this->_schedule = $schedule;
        $this->_timer = $timer;
        parent::__construct($config);
    }

    public function getSchedule(): ScheduleInterface
    {
        return $this->_schedule;
    }

    /**
     * @deprecated
     */
    public function getSummaryForDisplay(): string
    {
        return sprintf('%d#%s[%s] %d#%s', $this->_schedule->id, $this->_schedule->name, $this->_schedule->handle, $this->_timer->id, $this->_timer->uid);
    }

    public function run(): void
    {
        $this->_schedule->run();
    }

    public function isDue($app): bool
    {
        $date = new \DateTime('now');
        if ($this->timezone) {
            $date->setTimezone($this->timezone);
        }
        return (new CronExpression($this->_timer->getCronExpression()))->isDue($date);
    }
}