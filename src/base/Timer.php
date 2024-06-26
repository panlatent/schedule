<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\base;

use Craft;
use craft\base\SavableComponent;
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
     * @return array
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [['enabled'], 'required'];
        $rules[] = [['scheduleId', 'sortOrder'], 'integer'];
        $rules[] = [['enabled'], 'boolean'];
        return $rules;
    }

    /**
     * @deprecated
     */
    public function isValid(): bool
    {
        return true;
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
}