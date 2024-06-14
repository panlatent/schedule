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
     * @deprecated since 1.0.0
     */
    public function isValid(): bool;

    public function isDue(): bool;

    public function getSchedule(): Schedule;
}