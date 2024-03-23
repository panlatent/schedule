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
    public ?int $scheduleId = null;

    /**
     * @var string|null
     */
    public ?string $minute = null;

    /**
     * @var string|null
     */
    public ?string $hour = null;

    /**
     * @var string|null
     */
    public ?string $day = null;

    /**
     * @var string|null
     */
    public ?string $month = null;

    /**
     * @var string|null
     */
    public ?string $week = null;

    /**
     * @var bool|null
     */
    public ?bool $enabled = true;

    /**
     * @var int|null
     */
    public ?int $sortOrder = null;

    /**
     * @var string|null
     */
    public ?string $uid = null;
}