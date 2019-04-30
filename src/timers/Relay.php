<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\timers;

use Craft;
use DateInterval;
use panlatent\schedule\base\Schedule;
use panlatent\schedule\base\Timer;

/**
 * Class Relay
 *
 * @package panlatent\schedule\timers
 * @author Panlatent <panlatent@gmail.com>
 */
class Relay extends Timer
{
    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('schedule', 'Relay');
    }

    // Properties
    // =========================================================================

    /**
     * @var int
     */
    public $wait = 0;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['wait'], 'integer'];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function getCronExpression(): string
    {
        /** @var Schedule $schedule */
        $schedule = $this->getSchedule();

        if (!$schedule->getLastFinishedDate()) {
            return '* * * * * *';
        }

        $date = $schedule->getLastFinishedDate()->add(new DateInterval($this->wait . 'M'));

        return $date->format('i H d m * *');
    }

    /**
     * @inheritdoc
     */
    public function getCronDescription(): string
    {
        return Craft::t('schedule', 'Wait {wait} minutes after last executed', [
            'wait' => $this->wait,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('schedule/_components/timers/Relay', [
            'timer' => $this,
        ]);
    }
}