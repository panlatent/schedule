<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\records;

use craft\db\ActiveRecord;

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
    public static function tableName()
    {
        return '{{%schedulegroups}}';
    }
}