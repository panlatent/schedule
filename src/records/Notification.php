<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\records;

use craft\db\ActiveRecord;
use panlatent\schedule\db\Table;

/**
 * Class Notification
 *
 * @package panlatent\schedule\records
 * @property int $id
 * @property int $scheduleId
 * @property string $handle
 * @property string $settings
 * @property bool $enabled
 * @property int $sortOrder
 * @author Ryssbowh <boris@puzzlers.run>
 */
class Notification extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Table::SCHEDULENOTIFICATIONS;
    }
}