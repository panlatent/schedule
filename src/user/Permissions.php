<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2019 panlatent@gmail.com
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

    const MANAGE_SCHEDULES = 'schedule-manageSchedules';
    const MANAGE_LOGS = 'schedule-manageLogs';
    const MANAGE_SETTINGS = 'schedule-manageSettings';
}