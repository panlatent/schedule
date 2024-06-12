<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\base;

use Craft;
use craft\base\SavableComponent;
use panlatent\schedule\helpers\CronHelper;
use panlatent\schedule\models\Schedule;
use panlatent\schedule\Plugin;
use yii\base\InvalidConfigException;

/**
 * Class Timer
 *
 * @package panlatent\schedule\base
 * @property ScheduleInterface $schedule
 * @author Panlatent <panlatent@gmail.com>
 */
abstract class Timer extends SavableComponent implements TimerInterface
{
    // Traits
    // =========================================================================

    use TimerTrait;

    // Properties
    // =========================================================================

    /**
     * @var Schedule|null
     */
    private ?Schedule $_schedule = null;

    // Public Methods
    // =========================================================================

    /**
     * @return string
     */
    public function __toString()
    {
        return Craft::t('schedule', '# {order}' , [
            'order' => (int)$this->sortOrder
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [['scheduleId', 'enabled'], 'required'];
        $rules[] = [['scheduleId', 'sortOrder'], 'integer'];
        $rules[] = [['minute', 'hour', 'day', 'month', 'week'], 'string'];
        $rules[] = [['enabled'], 'boolean'];
        $rules[] = [['minute', 'hour', 'day', 'month', 'week'], function($property) {
            if ($this->$property === null || $this->$property === '') {
                $this->$property = '*';
            }
        }];

        return $rules;
    }

    public function trigger(): bool
    {
        return $this->getSchedule()->run();
    }

    /**
     * @inheritdoc
     */
    public function isValid(): bool
    {
        return $this->getSchedule()->isValid() && $this->enabled;
    }

    /**
     * @inheritdoc
     */
    public function getCronExpression(): string
    {
        return sprintf('%s %s %s %s %s', $this->minute, $this->hour, $this->day, $this->month, $this->week);
    }

    /**
     * @return Schedule
     * @throws InvalidConfigException
     */
    public function getSchedule(): Schedule
    {
        if ($this->_schedule === null) {
            if ($this->scheduleId === null) {
                throw new InvalidConfigException('The timer missing its schedule ID');
            }
            $this->_schedule = Plugin::getInstance()->schedules->getScheduleById($this->scheduleId);
            throw new InvalidConfigException('Invalid schedule ID: ' . $this->scheduleId);
        }

        return $this->_schedule;
    }

    /**
     * @param Schedule $schedule
     */
    public function setSchedule(Schedule $schedule): void
    {
        $this->_schedule = $schedule;
    }

    /**
     * @return string
     */
    public function getCronDescription(): string
    {
        return CronHelper::toDescription($this->getCronExpression());
    }

    /**
     * @param string $cron
     */
    public function setCronExpression(string $cron): void
    {
        [$this->minute, $this->hour, $this->day, $this->month, $this->week, ] = explode(' ', $cron);
    }
}