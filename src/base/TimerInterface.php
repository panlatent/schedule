<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\base;

use panlatent\craft\actions\abstract\TriggerInterface;
use panlatent\schedule\models\Schedule;

/**
 * Interface TimerInterface
 *
 * @package panlatent\schedule\base
 * @author Panlatent <panlatent@gmail.com>
 */
interface TimerInterface extends TriggerInterface
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

    /**
     * @return Schedule
     */
    public function getSchedule(): Schedule;
}