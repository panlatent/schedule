<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\db;

/**
 * Class Table
 *
 * @package panlatent\schedule\db
 * @author Panlatent <panlatent@gmail.com>
 */
abstract class Table
{
    const SCHEDULES = '{{%schedules}}';
    const SCHEDULEGROUPS = '{{%schedulegroups}}';
    const SCHEDULELOGS = '{{%schedulelogs}}';
    const SCHEDULETIMERS = '{{%scheduletimers}}';
}