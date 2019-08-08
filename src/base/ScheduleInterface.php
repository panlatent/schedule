<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
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
     * @return bool
     */
    public static function isRunnable(): bool;

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