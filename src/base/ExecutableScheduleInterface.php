<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\base;

/**
 * Interface ExecutableScheduleInterface
 *
 * @package panlatent\schedule\base
 * @author Panlatent <panlatent@gmail.com>
 */
interface ExecutableScheduleInterface extends ScheduleInterface
{
    /**
     * @return mixed
     */
    public function execute();
}