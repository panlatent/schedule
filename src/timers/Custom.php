<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
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
    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('schedule', 'Custom');
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getSettingsHtml(): ?string
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