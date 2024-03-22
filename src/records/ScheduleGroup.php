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
 * Class ScheduleGroup
 *
 * @package panlatent\schedule\records
 * @property int $id
 * @property string $name
 * @author Panlatent <panlatent@gmail.com>
 */
class ScheduleGroup extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return Table::SCHEDULEGROUPS;
    }
}