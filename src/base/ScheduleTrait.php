<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\base;

/**
 * Trait ScheduleTrait
 *
 * @package panlatent\schedule\base
 * @author Panlatent <panlatent@gmail.com>
 */
trait ScheduleTrait
{
    public $groupId;

    public $name;

    public $handle;

    public $minute;

    public $hour;

    public $day;

    public $month;

    public $week;

    public $user;

    public $sortOrder;
}