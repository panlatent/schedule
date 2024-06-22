<?php

namespace panlatent\schedule\records;

use craft\db\ActiveRecord;
use panlatent\schedule\db\Table;

/**
 * @property int $id
 * @property string $type
 * @property string $settings
 */
class Action extends ActiveRecord
{
    public static function tableName(): string
    {
        return Table::ACTIONS;
    }
}