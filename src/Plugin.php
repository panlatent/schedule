<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule;

use Craft;
use craft\base\Model;
use craft\events\RebuildConfigEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\helpers\UrlHelper;
use craft\services\ProjectConfig;
use craft\services\UserPermissions;
use craft\services\Utilities;
use craft\web\Response;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use panlatent\craft\actions\bundle\Bundle;
use panlatent\schedule\console\SchedulesController;
use panlatent\schedule\models\Settings;
use panlatent\schedule\services\Actions;
use panlatent\schedule\services\Logs;
use panlatent\schedule\services\Schedules;
use panlatent\schedule\services\Timers;
use panlatent\schedule\user\Permissions;
use panlatent\schedule\utilities\ActionRunner;
use panlatent\schedule\web\twig\CraftVariableBehavior;
use yii\base\Event;
use yii\console\Application as ConsoleApplication;

/**
 * Class Plugin
 *
 * @package panlatent\schedule
 * @method Settings getSettings()
 * @property-read Actions $actions
 * @property-read Schedules $schedules
 * @property-read Timers $timers
 * @property-read Settings $settings
 * @author Panlatent <panlatent@gmail.com>
 */
class Plugin extends \craft\base\Plugin
{
    public const EDITION_LITE = 'lite';
    public const EDITION_PRO = 'pro';

    // Properties
    // =========================================================================

    /**
     * @var Plugin
     * @deprecated since 1.0.0 Use Plugin::getInstance()
     */
    public static Plugin $plugin;

    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;
    public bool $hasCpSection = true;

    // Public Methods
    // =========================================================================

    public static function config(): array
    {
        return [
            'components' => [
                'actions' => Actions::class,
                'schedules' => Schedules::class,
                'timers' => Timers::class,
                'logs' => Logs::class,
            ],
        ];
    }

    public static function editions(): array
    {
        return [
            self::EDITION_LITE,
            self::EDITION_PRO,
        ];
    }

    public function init(): void
    {
        parent::init();
        self::$plugin = $this;
        Craft::setAlias('@schedule', $this->getBasePath());
        $this->name = Craft::t('schedule', 'Schedule');

        if (Craft::$app instanceof ConsoleApplication) {
            Craft::$app->controllerMap['schedules'] = SchedulesController::class;
        }

        Craft::$app->onInit(function() {
            $this->_registerCpRoutes();
            $this->_registerProjectConfigEvents();
            $this->_registerUserPermissions();
            $this->_registerVariables();
            $this->_registerUtilities();

            Bundle::register();

            if ($this->settings->enabledWebCron) {
                $this->_registerWebCron();
            }
        });
    }

    /**
     * @inheritdoc
     */
    public function getCpNavItem(): ?array
    {
        $ret = parent::getCpNavItem();
        $ret['label'] = $this->getSettings()->getCustomCpNavName() ?? $this->name;
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
            $schedules = $this->schedules;
            $timers = $this->timers;

            $config = [];
            foreach ($schedules->getStaticSchedules() as $schedule) {
                $scheduleConfig = $schedules->getScheduleConfig($schedule);
                $scheduleConfig['timers'] = [];
                foreach ($schedule->getTimers() as $timer) {
                    $scheduleConfig['timers'][$timer->uid] = $timers->getTimerConfig($timer);
                }
                $config[$schedule->uid] = $scheduleConfig;
            }
            $event->config['schedule']['schedules'] = $config;
        });

        $config
            ->onAdd('schedule.schedules.{uid}', $this->schedules->handleChangeSchedule(...))
            ->onUpdate('schedule.schedules.{uid}', $this->schedules->handleChangeSchedule(...))
            ->onRemove('schedule.schedules.{uid}', $this->schedules->handleDeleteSchedule(...))
            ->onAdd('schedule.schedules.{uid}.timers.{uid}', $this->timers->handleChangeTimer(...))
            ->onUpdate('schedule.schedules.{uid}.timers.{uid}', $this->timers->handleChangeTimer(...))
            ->onRemove('schedule.schedules.{uid}.timers.{uid}', $this->timers->handleDeleteTimer(...))
        ;
    }

    /**
     * Register user permissions.
     */
    private function _registerUserPermissions(): void
    {
        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, static function(RegisterUserPermissionsEvent $event) {
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

    public function _registerCpRoutes(): void
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, static function (RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, [
                'schedule/groups/<groupId:\d+|static>' => ['template' => 'schedule'],
                'schedule/new' => 'schedule/schedules/edit-schedule',
                'schedule/<scheduleId:\d+>' => 'schedule/schedules/edit-schedule',
//                'schedule/<scheduleId:\d+>/timers' => ['template' => 'schedule/timers'],
//                'schedule/<scheduleId:\d+>/timers/new' => 'schedule/timers/edit-timer',
//                'schedule/<scheduleId:\d+>/timers/<timerId:\d+>' => 'schedule/timers/edit-timer',
//                'schedule/<scheduleId:\d+>/logs' => ['template' => 'schedule/_logs'],
//                'schedule/<scheduleId:\d+>/logs/<logId:\d+>' => ['template' => 'schedule/logs/_view'],
            ]);
        });
    }

    /**
     * Register the plugin template variable.
     */
    private function _registerVariables(): void
    {
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, static function(Event $event) {
            /** @var CraftVariable $variable */
            $variable = $event->sender;
            $variable->attachBehavior('schedule', CraftVariableBehavior::class);
        });
    }

    private function _registerWebCron(): void
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_SITE_URL_RULES, function (RegisterUrlRulesEvent $event) {
            $event->rules[$this->settings->endpoint] = 'schedule/web-cron/trigger';
        });
    }

    private function _registerUtilities(): void
    {
        Event::on(Utilities::class, Utilities::EVENT_REGISTER_UTILITIES, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = ActionRunner::class;
        });
    }
}