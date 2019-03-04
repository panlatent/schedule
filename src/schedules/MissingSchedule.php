<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
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
 */
class MissingSchedule extends Schedule
{
    use MissingComponentTrait;

    /**
     * @inheritdoc
     */
    public function build(Builder $builder)
    {
        Craft::warning('Missing build a schedule', __METHOD__);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        throw new NotSupportedException();
    }
}