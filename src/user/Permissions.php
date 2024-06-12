<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\user;

/**
 * Class Permissions
 *
 * @package panlatent\schedule\user
 * @author Panlatent <panlatent@gmail.com>
 */
abstract class Permissions
{
    // Constants
    // =========================================================================

    public const MANAGE_SCHEDULES = 'schedule-manageSchedules';
    public const MANAGE_LOGS = 'schedule-manageLogs';
}