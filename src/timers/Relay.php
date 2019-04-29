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
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('schedule/_components/timers/Relay', [
            'timer' => $this,
        ]);
    }
}