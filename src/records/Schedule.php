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
 * Class Schedule
 *
 * @package panlatent\schedule\records
 * @property int $id
 * @property int $groupId
 * @property string $name
 * @property string $handle
 * @property string $description
 * @property string $type
 * @property string $user
 * @property string $settings
 * @property bool $enabled
 * @property bool $enabledLog
 * @property int $lastStartedTime
 * @property int $lastFinishedTime
 * @property bool $lastStatus
 * @property int $sortOrder
 * @author Panlatent <panlatent@gmail.com>
 */
class Schedule extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Table::SCHEDULES;
    }
}