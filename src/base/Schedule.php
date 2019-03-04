<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\base;

use craft\base\SavableComponent;

/**
 * Class Schedule
 *
 * @package panlatent\schedule\base
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

        ];
    }

    /**
     * Returns cron expression.
     *
     * @return string
     */
    public function getCronExpression(): string
    {
        return sprintf('%s %s %s %s %s *', $this->minute ?: '*', $this->hour ?: '*', $this->day ?: '*', $this->month ?: '*', $this->week ?: '*');
    }

    /**
     * Returns cron expression description.
     *
     * @return string
     */
    public function getCronDescription(): string
    {
        return '';
    }
}