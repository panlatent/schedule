<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\schedules;

use Craft;
use craft\base\MissingComponentTrait;
use panlatent\schedule\base\Schedule;
use panlatent\schedule\Builder;
use yii\base\NotSupportedException;

/**
 * Class MissingSchedule
 *
 * @package panlatent\schedule\schedules
 * @author Panlatent <panlatent@gmail.com>
 * @deprecated since 1.0.0
 */
class MissingSchedule extends Schedule
{
    use MissingComponentTrait;

    /**
     * @inheritdoc
     */
    public function build(Builder $builder): void
    {
        Craft::warning('Missing build a schedule', __METHOD__);
    }

    /**
     * @inheritdoc
     */
    public function execute(int $logId = null): bool
    {
        throw new NotSupportedException();
    }
}