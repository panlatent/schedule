<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule;

use Craft;
use craft\helpers\UrlHelper;
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
 * @method Settings getSettings()
 * @property-read Settings $settings
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
    public $schemaVersion = '0.2.0-alpha.2';

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
        if ($this->getSettings()->customName && $this->getSettings()->modifyPluginName) {
            $this->name = Craft::t('schedule', 'Schedule');
        }

        // Replace omnilight controller to this plugin controller in console.
        if (Craft::$app instanceof Application) {
            if (isset(Craft::$app->controllerMap['schedule']) && Craft::$app->controllerMap['schedule'] == 'omnilight\scheduling\ScheduleController') {
                unset(Craft::$app->controllerMap['schedule']);
                Craft::$app->controllerMap['schedules'] = SchedulesController::class;
            }
        }

        $this->_registerCpRoutes();
        $this->_registerVariables();
        $this->_setComponents();
    }

    /**
     * @inheritdoc
     */
    public function getCpNavItem()
    {
        $ret = parent::getCpNavItem();

        if ($this->getSettings()->customName) {
            $ret['label'] = $this->getSettings()->customName;
        } else {
            $ret['label'] = Craft::t('schedule', 'Schedule');
        }

        $ret['subnav'][''] = [
            'label' => Craft::t('schedule', 'Schedules'),
            'url' => 'schedule',
        ];

        $ret['subnav']['logs'] = [
            'label' => Craft::t('schedule', 'Logs'),
            'url' => 'schedule/logs',
        ];

        $ret['subnav']['settings'] = [
            'label' => Craft::t('schedule', 'Settings'),
            'url' => 'schedule/settings',
        ];

        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function getSettingsResponse()
    {
        return Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('schedule/settings'));
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