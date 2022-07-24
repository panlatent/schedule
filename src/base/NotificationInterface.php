<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\base;

use craft\base\Model;
use craft\base\SavableComponentInterface;
use panlatent\schedule\base\ScheduleInterface;

/**
 * Interface NotificationInterface
 *
 * @package panlatent\schedule\base
 * @author Ryssbowh <boris@puzzlers.run>
 */
interface NotificationInterface extends SavableComponentInterface
{
    /**
     * Get notification handle
     * 
     * @return string
     */
    public function getHandle(): string;

    /**
     * Get notification name
     * 
     * @return string
     */
    public function getName(): string;

    /**
     * Get notification description
     * 
     * @return string
     */
    public function getDescription(): string;

    /**
     * Set settings
     * 
     * @param string|array $settings
     */
    public function setSettings($settings);

    /**
     * Get settings model
     * 
     * @return Model
     */
    public function getSettingsModel(): Model;

    /**
     * @return ScheduleInterface
     */
    public function getSchedule(): ScheduleInterface;

    /**
     * Notify of a schedule failure
     * 
     * @param  bool   $success
     * @param  ?array $log
     * @return bool
     */
    public function notify(bool $success, ?array $log): bool;

    /**
     * Get the template used to render the settings
     * 
     * @return string
     */
    public function getSettingsTemplate(): string;
}