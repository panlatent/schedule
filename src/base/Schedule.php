<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\base;

use craft\base\SavableComponent;
use craft\validators\HandleValidator;
use panlatent\schedule\helpers\CronHelper;

/**
 * Class Schedule
 *
 * @package panlatent\schedule\base
 * @property-read string $cronExpression
 * @property-read string $cronDescription
 * @author Panlatent <panlatent@gmail.com>
 */
abstract class Schedule extends SavableComponent implements ScheduleInterface
{
    // Traits
    // =========================================================================

    use ScheduleTrait;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'handle', 'minute', 'hour', 'day', 'month', 'week', 'timer'], 'required'],
            [['groupId'], 'integer'],
            [['name', 'handle', 'description', 'minute', 'hour', 'day', 'month', 'week', 'user', 'timer'], 'string'],
            [['handle'], HandleValidator::class]
        ];
    }

    /**
     * Returns cron expression.
     *
     * @return string
     */
    public function getCronExpression(): string
    {
        return CronHelper::toCronExpression([$this->minute, $this->hour, $this->day, $this->month, $this->week]);
    }

    /**
     * Returns cron expression description.
     *
     * @return string
     */
    public function getCronDescription(): string
    {
        return CronHelper::toDescription($this->getCronExpression());
    }
}