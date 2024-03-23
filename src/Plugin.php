<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule;

use Craft;
use craft\base\Model;
use craft\events\ConfigEvent;
use craft\events\RebuildConfigEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\helpers\UrlHelper;
use craft\services\ProjectConfig;
use craft\services\UserPermissions;
use craft\web\Response;
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
    public static Plugin $plugin;

    /**
     * @var string
     */
    public string $schemaVersion = '0.5.0';

    /**
     * @var string|null
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
    public function init(): void
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
        $this->_registerProjectConfigEvents();
        $this->_registerUserPermissions();
        $this->_registerVariables();
    }

    /**
     * @inheritdoc
     */
    public function getCpNavItem(): ?array
    {
        $ret = parent::getCpNavItem();
        $ret['label'] = $this->getSettings()->getCustomCpNavName() ?? $this->name;

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
    public function getSettingsResponse(): Response
    {
        return Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('schedule/settings'));
    }

    // Protected Methods
    // =========================================================================

    /**
     * @return Model|null
     */
    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    // Private Methods
    // =========================================================================

    private function _registerProjectConfigEvents(): void
    {
        $config = Craft::$app->getProjectConfig();

        Event::on(ProjectConfig::class, ProjectConfig::EVENT_REBUILD, function(RebuildConfigEvent $event) {
            $schedules = $this->getSchedules();
            $timers = $this->getTimers();

            $config = [];
            foreach ($schedules->getStaticSchedules() as $schedule) {
                $scheduleConfig = $this->getSchedules()->getScheduleConfig($schedule);
                $scheduleConfig['timers'] = [];
                foreach ($schedule->getTimers() as $timer) {
                    $scheduleConfig['timers'][$timer->uid] = $timers->getTimerConfig($timer);
                }
                $config[$schedule->uid] = $scheduleConfig;
            }
            $event->config['schedule']['schedules'] = $config;
        });

        $config->onAdd('schedule.schedules.{uid}', function(ConfigEvent $event) {
            $this->getSchedules()->handleChangeSchedule($event);
        });
        $config->onUpdate('schedule.schedules.{uid}', function (ConfigEvent $event) {
            $this->getSchedules()->handleChangeSchedule($event);
        });
        $config->onRemove('schedule.schedules.{uid}', function (ConfigEvent $event) {
            $this->getSchedules()->handleDeleteSchedule($event);
        });

        $config->onAdd('schedule.schedules.{uid}.timers.{uid}', function(ConfigEvent $event) {
            $this->getTimers()->handleChangeTimer($event);
        });
        $config->onUpdate('schedule.schedules.{uid}.timers.{uid}', function (ConfigEvent $event) {
            $this->getTimers()->handleChangeTimer($event);
        });
        $config->onRemove('schedule.schedules.{uid}.timers.{uid}', function (ConfigEvent $event) {
            $this->getTimers()->handleDeleteTimer($event);
        });
    }

    /**
     * Register user permissions.
     */
    private function _registerUserPermissions(): void
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
                ],
            ];
        });
    }

    /**
     * Register the plugin template variable.
     */
    private function _registerVariables(): void
    {
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            /** @var CraftVariable $variable */
            $variable = $event->sender;
            $variable->attachBehavior('schedule', CraftVariableBehavior::class);
        });
    }
}