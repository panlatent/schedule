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
 * @property int $sortOrder
 * @property \DateTime $dateLastStarted
 * @property \DateTime $dateLastFinished
 * @author Panlatent <panlatent@gmail.com>
 */
class Schedule extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%schedules}}';
    }
}