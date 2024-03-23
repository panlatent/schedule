<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
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
    public ?int $groupId = null;

    /**
     * @var string|null
     */
    public ?string $name = null;

    /**
     * @var string|null
     */
    public ?string $handle = null;

    /**
     * @var string|null
     */
    public ?string $description = null;

    /**
     * @var string|null
     */
    public ?string $user = null;

    /**
     * Static schedules are stored in the project config.
     * @var bool
     */
    public bool $static = false;

    /**
     * @var bool
     */
    public bool $enabled = true;

    /**
     * @var bool|null
     */
    public ?bool $enabledLog = null;

    /**
     * @var int|null
     */
    public ?int $lastStartedTime = null;

    /**
     * @var int|null
     */
    public ?int $lastFinishedTime = null;

    /**
     * @var string|null
     */
    public ?string $lastStatus = null;

    /**
     * @var int|null
     */
    public ?int $sortOrder = null;

    /**
     * @var string|null
     */
    public ?string $uid = null;
}