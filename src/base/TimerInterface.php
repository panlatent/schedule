<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\base;

/**
 * Interface TimerInterface
 *
 * @package panlatent\schedule\base
 * @author Panlatent <panlatent@gmail.com>
 */
interface TimerInterface
{
    /**
     * @see \panlatent\schedule\services\Timers::getAllTimers()
     *
     * @return bool whether to run the timer.
     */
    public function isValid(): bool;

    /**
     * Returns cron expression.
     *
     * @return string
     */
    public function getCronExpression(): string;
}