<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule;

use Craft;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\ArrayHelper;
use craft\web\UrlManager;
use panlatent\schedule\console\ScheduleController;
use panlatent\schedule\models\Settings;
use panlatent\schedule\services\Schedules;
use yii\base\Event;
use yii\console\Application;

/**
 * Class Plugin
 *
 * @package panlatent\schedule
 * @property-read Builder $builder
 * @property-read Schedules $schedules
 * @author Panlatent <panlatent@gmail.com>
 */
class Plugin extends \craft\base\Plugin
{
    /**
     * @var Plugin
     */
    public static $plugin;

    /**
     * @var string
     */
    public $schemaVersion = '0.1.0';

    /**
     * @var string
     */
    public $t9nCategory = 'schedule';

    /**
     * @inheritdoc
     */
    public function __construct($id, $parent = null, array $config = [])
    {
        $components = ArrayHelper::remove($config, 'components', []);

        if (!isset($components['builder'])) {
            $components['builder'] = [
                'class' => Builder::class
            ];
        }

        if (!isset($components['schedules'])) {
            $components['schedules'] = [
                'class' => Schedules::class
            ];
        }

        $config['components'] = $components;

        parent::__construct($id, $parent, $config);
    }

    /**
     * Init.
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Craft::setAlias('@schedule', $this->getBasePath());
        $this->name = Craft::t('schedule', 'Schedule');

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function (RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, [
                'schedule' => 'schedule/schedules',
                'schedule/groups/<groupId:\d+>' => 'schedule/schedules',
                'schedule/new' => 'schedule/schedules/edit-schedule',
                'schedule/<scheduleId:\d+>' => 'schedule/schedules/edit-schedule',
            ]);
        });

        // Replace omnilight controller to this plugin controller in console.
        if (Craft::$app instanceof Application) {
            if (isset(Craft::$app->controllerMap['schedule']) && Craft::$app->controllerMap['schedule'] == 'omnilight\scheduling\ScheduleController') {
                Craft::$app->controllerMap['schedule'] = ScheduleController::class;
            }
        }

        Craft::info(
            Craft::t(
                'schedule',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    /**
     * @return Builder|object|null
     */
    public function getBuilder()
    {
        return $this->get('builder');
    }

    /**
     * @return Schedules
     */
    public function getSchedules(): Schedules
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get('schedules');
    }

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
}