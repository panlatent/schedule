<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\base;

/**
 * Trait TimerTrait
 *
 * @package panlatent\schedule\base
 * @author Panlatent <panlatent@gmail.com>
 */
trait TimerTrait
{
    // Properties
    // =========================================================================

    /**
     * @var int|null
     */
    public $scheduleId;

    /**
     * @var string|null
     */
    public $minute;

    /**
     * @var string|null
     */
    public $hour;

    /**
     * @var string|null
     */
    public $day;

    /**
     * @var string|null
     */
    public $month;

    /**
     * @var string|null
     */
    public $week;

    /**
     * @var bool|null
     */
    public $enabled = true;

    /**
     * @var int|null
     */
    public $sortOrder;
}