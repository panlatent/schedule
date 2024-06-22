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
 * @property int $actionId
 * @property int $onSuccess
 * @property int $onFailed
 * @property bool $enabled
 * @property int $sortOrder
 * @author Panlatent <panlatent@gmail.com>
 */
class Schedule extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return Table::SCHEDULES;
    }
}