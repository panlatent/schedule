<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\base;

use craft\base\SavableComponentInterface;
use panlatent\schedule\Builder;
use panlatent\schedule\models\ScheduleLog;

/**
 * Interface ScheduleInterface
 *
 * @package panlatent\schedule\base
 * @author Panlatent <panlatent@gmail.com>
 */
interface ScheduleInterface extends SavableComponentInterface
{
    /**
     * @return bool whether can execute run method
     */
    public static function isRunnable(): bool;

    /**
     * @return bool whether to run the schedule
     */
    public function isValid(): bool;

    /**
     * @param Builder $builder
     */
    public function build(Builder $builder);

    /**
     * @return bool
     */
    public function run(): bool;

    /**
     * @param ScheduleLog $log
     * @return string
     */
    public function renderLogContent(ScheduleLog $log): string;
}