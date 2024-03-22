<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\events;

use panlatent\schedule\Builder;
use yii\base\Event;

/**
 * Class ScheduleBuildEvent
 *
 * @package panlatent\schedule\events
 * @author Panlatent <panlatent@gmail.com>
 */
class ScheduleBuildEvent extends Event
{
    /**
     * @var Builder|null
     */
    public ?Builder $builder = null;

    /**
     * @var array[]|null
     */
    public ?array $events = null;

    /**
     * @var bool
     */
    public bool $isValid = true;
}