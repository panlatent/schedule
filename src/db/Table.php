<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
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
    const SCHEDULENOTIFICATIONS = '{{%schedulenotifications}}';
}