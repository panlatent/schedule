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
    public const ACTIONS = '{{%schedule_actions}}';
    public const SCHEDULES = '{{%schedule_schedules}}';
    public const SCHEDULE_GROUPS = '{{%schedule_schedulegroups}}';
    public const TIMERS = '{{%schedule_timers}}';

    /**
     * @deprecated since 1.0.0
     */
    const SCHEDULEGROUPS = '{{%schedulegroups}}';

    /**
     * @deprecated since 1.0.0
     */
    const SCHEDULELOGS = '{{%schedulelogs}}';

    /**
     * @deprecated since 1.0.0
     */
    const SCHEDULETIMERS = '{{%scheduletimers}}';
}