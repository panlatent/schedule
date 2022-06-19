<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule;

use Craft;
use craft\base\Model;
use craft\events\RegisterUserPermissionsEvent;
use craft\helpers\UrlHelper;
use craft\services\UserPermissions;
use craft\web\twig\variables\CraftVariable;
use panlatent\schedule\console\SchedulesController;
use panlatent\schedule\models\Settings;
use panlatent\schedule\plugin\Routes;
use panlatent\schedule\plugin\Services;
use panlatent\schedule\user\Permissions;
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

    use Routes;
    use Services;

    // Properties
    // =========================================================================

    /**
     * @var Plugin
     */
    public static $plugin;

    /**
     * @var string
     */
    public string $schemaVersion = '0.4.0';

    /**
     * @var string
     */
    public ?string $t9nCategory = 'schedule';

    /**
     * @var bool
     */
    public bool $hasCpSettings = true;

    /**
     * @var bool
     */
    public bool $hasCpSection = true;

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

        if (strval($this->getSettings()->customName) !== "") {
            $this->name = $this->getSettings()->customName;
        } else {
            $this->name = Craft::t('schedule', 'Schedule');
        }

        // Replace omnilight controller to this plugin controller in console.
        if (Craft::$app instanceof Application) {
            if (isset(Craft::$app->controllerMap['schedule']) && Craft::$app->controllerMap['schedule'] == 'omnilight\scheduling\ScheduleController') {
                unset(Craft::$app->controllerMap['schedule']);
                Craft::$app->controllerMap['schedules'] = SchedulesController::class;
            }
        }

        $this->_setComponents();
        $this->_registerCpRoutes();
        $this->_registerUserPermissions();
        $this->_registerVariables();
    }

    /**
     * @inheritdoc
     */
    public function getCpNavItem(): ?array
    {
        $ret = parent::getCpNavItem();

        if (strval($this->getSettings()->customCpNavName)) {
            $ret['label'] = $this->getSettings()->customCpNavName;
        } else {
            $ret['label'] = $this->name;
        }

        $user = Craft::$app->getUser();
        if ($user->checkPermission(Permissions::MANAGE_SCHEDULES)) {
            $ret['subnav']['schedules'] = [
                'label' => Craft::t('schedule', 'Schedules'),
                'url' => 'schedule',
            ];
        }

        if ($user->checkPermission(Permissions::MANAGE_LOGS)) {
            $ret['subnav']['logs'] = [
                'label' => Craft::t('schedule', 'Logs'),
                'url' => 'schedule/logs',
            ];
        }

        if (Craft::$app->getConfig()->getGeneral()->allowAdminChanges && $user->getIsAdmin()) {
            $ret['subnav']['settings'] = [
                'label' => Craft::t('schedule', 'Settings'),
                'url' => 'schedule/settings',
            ];
        }

        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function getSettingsResponse(): mixed
    {
        return Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('schedule/settings'));
    }

    // Protected Methods
    // =========================================================================

    /**
     * @return Settings
     */
    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    // Private Methods
    // =========================================================================

    /**
     * Register user permissions.
     */
    private function _registerUserPermissions()
    {
        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function(RegisterUserPermissionsEvent $event) {
            $event->permissions[] = [
                'heading' => Craft::t('schedule', 'Schedules'),
                'permissions' => [
                    Permissions::MANAGE_SCHEDULES => [
                        'label' => Craft::t('schedule', 'Manage Schedules'),
                    ],
                    Permissions::MANAGE_LOGS => [
                        'label' => Craft::t('schedule', 'Manage Logs'),
                    ],
                ]
            ];
        });
    }

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