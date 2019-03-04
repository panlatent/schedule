<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\schedules;

use Craft;
use panlatent\schedule\base\ExecutableScheduleInterface;
use panlatent\schedule\base\ExecutableScheduleTrait;
use panlatent\schedule\base\Schedule;
use yii\base\Event as BaseEvent;

/**
 * Class Event
 *
 * @package panlatent\schedule\schedules
 * @author Panlatent <panlatent@gmail.com>
 */
class Event extends Schedule implements ExecutableScheduleInterface
{
    // Traits
    // =========================================================================

    use ExecutableScheduleTrait;

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('schedule', 'Event');
    }

    // Properties
    // =========================================================================

    /**
     * @var string|null
     */
    public $className;

    /**
     * @var string|null
     */
    public $eventName;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function execute()
    {
        Craft::info("Event Schedule trigger event: {$this->className}::{$this->eventName}", __METHOD__);

        BaseEvent::trigger($this->className, $this->eventName);
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('schedule/_components/schedules/Event', [
            'schedule' => $this,
        ]);
    }
}