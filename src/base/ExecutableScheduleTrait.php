<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\base;

use panlatent\schedule\Builder;

/**
 * Trait ExecutableScheduleTrait
 *
 * @package panlatent\schedule\base
 * @author Panlatent <panlatent@gmail.com>
 */
trait ExecutableScheduleTrait
{
    /**
     * @inheritdoc
     */
    public function build(Builder $builder)
    {
        $builder->command('schedules/do-schedule ' . $this->id)
            ->cron($this->getCronExpression());
    }
}