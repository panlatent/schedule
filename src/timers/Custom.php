<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\timers;

use Craft;
use panlatent\schedule\base\Timer;

/**
 * Class Custom
 *
 * @package panlatent\schedule\timers
 * @author Panlatent <panlatent@gmail.com>
 */
class Custom extends Timer
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('schedule', 'Custom');
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('schedule/_components/timers/Custom', [
            'minute' => $this->minute,
            'hour' => $this->hour,
            'day' => $this->day,
            'month' => $this->month,
            'week' => $this->week,
        ]);
    }
}