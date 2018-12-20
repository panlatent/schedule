<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\schedules;

use panlatent\schedule\base\Schedule;
use yii\base\NotSupportedException;

/**
 * Class MissingSchedule
 *
 * @package panlatent\schedule\schedules
 * @author Panlatent <panlatent@gmail.com>
 */
class MissingSchedule extends Schedule
{
    public function execute()
    {
        throw new NotSupportedException();
    }
}