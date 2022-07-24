<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\base;

use craft\base\Model;
use craft\base\SavableComponent;
use craft\helpers\Html;
use craft\helpers\Json;
use craft\web\View;
use panlatent\schedule\Plugin;
use panlatent\schedule\base\ScheduleInterface;
use yii\base\InvalidConfigException;

/**
 * Class Notification
 *
 * @package panlatent\schedule\base
 * @author Ryssbowh <boris@puzzlers.run>
 */
abstract class Notification extends SavableComponent implements NotificationInterface
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $scheduleId;

    /**
     * @var boolean
     */
    public $enabled;

    /**
     * @var int
     */
    public $sortOrder;

    /**
     * @var DateTime
     */
    public $dateCreated;

    /**
     * @var DateTime
     */
    public $dateUpdated;

    /**
     * @var string
     */
    public $uid;

    /**
     * @var ScheduleInterface
     */
    protected $_schedule;

    /**
     * @var Model
     */
    protected $_settings;

    /**
     * @return ScheduleInterface
     */
    public function getSchedule(): ScheduleInterface
    {
        if ($this->_schedule == null and $this->scheduleId) {
            $this->_schedule = Plugin::getInstance()->getSchedules()->getScheduleById($this->scheduleId);
        }
        if (!$this->_schedule) {
            throw new InvalidConfigException('Invalid schedule ID: ' . $this->scheduleId);
        }
        return $this->_schedule;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return \Craft::t('schedule', '# {order}' , [
            'order' => (int)$this->sortOrder
        ]);
    }

    /**
     * @inheritDoc
     */
    public function afterValidate()
    {
        $this->settingsInstance->validate();
    }

    /**
     * @inheritDoc
     */
    public function hasErrors($attribute = null)
    {
        if ($attribute == 'settings') {
            return $this->settingsInstance->hasErrors();
        }
        return (parent::hasErrors($attribute) or $this->settingsInstance->hasErrors($attribute));
    }

    /**
     * @inheritDoc
     */
    public function attributes()
    {
        return array_merge(parent::attributes(), ['settings']);
    }

    /**
     * @inheritDoc
     */
    public function getSettings(): array
    {
        return $this->settingsInstance->attributes;
    }

    /**
     * @inheritDoc
     */
    public function setSettings($settings)
    {
        if (is_string($settings)) {
            $settings = Json::decode($settings);
        }
        $this->settingsInstance->setAttributes($settings);
    }

    /**
     * Get the settings instance
     * 
     * @return Model
     */
    protected function getSettingsInstance(): Model
    {
        if ($this->_settings === null) {
            $this->_settings = $this->getSettingsModel();
        }
        return $this->_settings;
    }
}