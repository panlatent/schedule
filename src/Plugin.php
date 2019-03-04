<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule;

use Craft;
use craft\web\twig\variables\CraftVariable;
use panlatent\schedule\console\SchedulesController;
use panlatent\schedule\models\Settings;
use panlatent\schedule\plugin\Routes;
use panlatent\schedule\plugin\Services;
use panlatent\schedule\web\twig\CraftVariableBehavior;
use yii\base\Event;
use yii\console\Application;

/**
 * Class Plugin
 *
 * @package panlatent\schedule
 * @author Panlatent <panlatent@gmail.com>
 */
class Plugin extends \craft\base\Plugin
{
    // Traits
    // =========================================================================

    use Routes, Services;

    // Properties
    // =========================================================================

    /**
     * @var Plugin
     */
    public static $plugin;

    /**
     * @var string
     */
    public $schemaVersion = '0.1.3';

    /**
     * @var string
     */
    public $t9nCategory = 'schedule';

    // Public Methods
    // =========================================================================

    /**
     * Init.
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Craft::setAlias('@schedule', $this->getBasePath());
        $this->name = Craft::t('schedule', 'Schedule');

        // Replace omnilight controller to this plugin controller in console.
        if (Craft::$app instanceof Application) {
            if (isset(Craft::$app->controllerMap['schedule']) && Craft::$app->controllerMap['schedule'] == 'omnilight\scheduling\ScheduleController') {
                unset(Craft::$app->controllerMap['schedule']);
                Craft::$app->controllerMap['schedules'] = SchedulesController::class;
            }
        }

        $this->_setComponents();
        $this->_registerCpRoutes();
        $this->_registerVariables();
    }

    // Protected Methods
    // =========================================================================

    /**
     * @return Settings
     */
    protected function createSettingsModel(): Settings
    {
        return new Settings();
    }

    /**
     * @return string
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('schedule/_settings', [
            'settings' => $this->getSettings(),
        ]);
    }

    // Private Methods
    // =========================================================================

    /**
     * Register the plugin template variable.
     */
    private function _registerVariables()
    {
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            /** @var CraftVariable $variable */
            $variable = $event->sender;
            $variable->attachBehavior('schedule', CraftVariableBehavior::class);
        });
    }
}