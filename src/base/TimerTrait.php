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
    public ?int $scheduleId = null;
    public ?bool $enabled = true;
    public ?int $sortOrder = null;
    public ?string $uid = null;
}