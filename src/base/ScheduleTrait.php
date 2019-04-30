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
    /**
     * @var int|null
     */
    public $groupId;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $handle;

    /**
     * @var string|null
     */
    public $description;

    /**
     * @var string|null
     */
    public $user;

    /**
     * @var bool|null
     */
    public $enabledLog;

    /**
     * @var int|null
     */
    public $lastStartedTime;

    /**
     * @var int|null
     */
    public $lastFinishedTime;

    /**
     * @var string|null
     */
    public $lastStatus;

    /**
     * @var int|null
     */
    public $sortOrder;
}