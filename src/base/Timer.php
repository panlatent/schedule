<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\base;

use Craft;
use craft\base\SavableComponent;
use panlatent\schedule\helpers\CronHelper;
use panlatent\schedule\Plugin;

/**
 * Class Timer
 *
 * @package panlatent\schedule\base
 * @property ScheduleInterface|null $schedule
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
     * @var ScheduleInterface|null
     */
    private $_schedule;

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
    public function rules()
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

    /**
     * @return ScheduleInterface|null
     */
    public function getSchedule()
    {
        if ($this->_schedule !== null) {
            return $this->_schedule;
        }

        if (!$this->scheduleId) {
            return null;
        }

        return $this->_schedule = Plugin::getInstance()->getSchedules()->getScheduleById($this->scheduleId);
    }

    /**
     * @param ScheduleInterface $schedule
     */
    public function setSchedule(ScheduleInterface $schedule)
    {
        $this->_schedule = $schedule;
    }

    /**
     * @inheritdoc
     */
    public function getCronExpression(): string
    {
        return sprintf('%s %s %s %s %s *', $this->minute, $this->hour, $this->day, $this->month, $this->week);
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
    public function setCronExpression(string $cron)
    {
        list($this->minute, $this->hour, $this->day, $this->month, $this->week, ) = explode(' ', $cron);
    }
}