<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\models;

use Craft;
use craft\base\Model;
use panlatent\schedule\base\ScheduleInterface;
use panlatent\schedule\Plugin;
use panlatent\schedule\records\ScheduleGroup as ScheduleGroupRecord;

/**
 * Class ScheduleGroup
 *
 * @package panlatent\schedule\models
 * @author Panlatent <panlatent@gmail.com>
 */
class ScheduleGroup extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var int|null
     */
    public $id;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var ScheduleInterface[]|null
     */
    private $_schedules;

    // Public Methods
    // =========================================================================

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->name;
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['name'], 'string'],
            [['name'], 'unique', 'targetClass' => ScheduleGroupRecord::class, 'targetAttribute' => 'name']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Craft::t('app', 'ID'),
            'name' => Craft::t('app', 'name'),
        ];
    }

    /**
     * @return ScheduleInterface[]
     */
    public function getSchedules(): array
    {
        if ($this->_schedules !== null) {
            return $this->_schedules;
        }

        if (!$this->id) {
            return [];
        }

        return $this->_schedules = Plugin::$plugin->getSchedules()->getSchedulesByGroupId($this->id);
    }
}