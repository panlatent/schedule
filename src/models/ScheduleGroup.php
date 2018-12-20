<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\models;

use Craft;
use craft\base\Model;
use panlatent\schedule\records\ScheduleGroup as ScheduleGroupRecord;

/**
 * Class ScheduleGroup
 *
 * @package panlatent\schedule\models
 * @author Panlatent <panlatent@gmail.com>
 */
class ScheduleGroup extends Model
{
    /**
     * @var int|null
     */
    public $id;

    /**
     * @var string|null
     */
    public $name;

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string'],
            [['name'], 'unique', 'targetClass' => ScheduleGroupRecord::class, 'targetAttribute' => 'name']
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Craft::t('app', 'ID'),
            'name' => Craft::t('app', 'name'),
        ];
    }
}