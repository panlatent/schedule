<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\records;

use craft\db\ActiveRecord;
use panlatent\schedule\db\Table;

/**
 * Class Timer
 *
 * @package panlatent\schedule\records
 * @property int $id
 * @property int $scheduleId
 * @property string $type
 * @property string $minute
 * @property string $hour
 * @property string $day
 * @property string $month
 * @property string $week
 * @property string $settings
 * @property bool $enabled
 * @property int $sortOrder
 * @author Panlatent <panlatent@gmail.com>
 */
class Timer extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Table::SCHEDULETIMERS;
    }
}