<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\console;

use Carbon\CarbonInterval;
use Craft;
use Carbon\Carbon;
use omnilight\scheduling\Event;
use panlatent\schedule\base\Schedule;
use panlatent\schedule\Plugin;
use panlatent\schedule\validators\CarbonStringIntervalValidator;
use yii\console\Controller;
use yii\helpers\Console;
use yii\validators\Validator;

/**
 * Class ScheduleController
 *
 * @package panlatent\schedule\console
 * @author Panlatent <panlatent@gmail.com>
 */
class SchedulesController extends Controller
{
    // Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    public $defaultAction = 'list';

    /**
     * @var bool|null Force flush schedule repository.
     */
    public $force;

    /**
     * @var bool|null Clear all logs.
     */
    public $all;

    /**
     * @var string|null Expiry offset for log clearing.
     */
    public $expire;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        $options = parent::options($actionID);
        switch ($actionID) {
            case 'run': // no break
            case 'listen':
                $options[] = 'force';
                break;
            case 'clear-logs':
                $options[] = 'all';
                $options[] = 'expire';
                break;
        }

        return $options;
    }

    /**
     * List all schedules.
     */
    public function actionList()
    {
        $schedules = Plugin::$plugin->getSchedules();

        $i = 0;
        if ($ungroupedSchedules = $schedules->getSchedulesByGroupId()) {
            $this->stdout(Craft::t('schedule', 'Ungrouped') . ": \n", Console::FG_YELLOW);
            foreach ($ungroupedSchedules as $schedule) {
                /** @var Schedule $schedule */
                $this->stdout(Console::renderColoredString("    > #{$i} %c{$schedule->handle}\n"));
                ++$i;
            }
        }

        foreach ($schedules->getAllGroups() as $group) {
            $this->stdout("{$group->name}: \n", Console::FG_YELLOW);
            foreach ($group->getSchedules() as $schedule) {
                // @var Schedule $schedule

                $this->stdout(Console::renderColoredString("    > #{$i} %c{$schedule->handle}\n"));
                ++$i;
            }
        }
    }

    /**
     * Run all schedules.
     *
     * @param Event[]|null $events
     */
    public function actionRun(array $events = null)
    {
        if ($events === null) {
            $events = Plugin::$plugin->createBuilder()
                ->build($this->force ?? false)
                ->dueEvents(Craft::$app);
        }

        if (empty($events)) {
            $this->stdout("No scheduled commands are ready to run.\n");
            return;
        }

        foreach ($events as $event) {
            $command = $event->getSummaryForDisplay();
            $this->stdout("Running scheduled command: {$command}\n");

            $event->run(Craft::$app);

            Craft::info("Running scheduled command: {$command}", __METHOD__);
        }

        Craft::info("Running scheduled event total: " . count($events), __METHOD__);
    }

    /**
     * Run a permanent command to call crons run command every minute
     *
     * @return void
     */
    public function actionListen()
    {
        if ($this->force === null) {
            $this->force = true;
        }

        if (!$this->force) {
            $this->stdout("(!) Notice: Force option is disable, all schedules updates will not be synchronized.\n");
        }

        $waitSeconds = $this->nextMinute();
        $this->stdout("Waiting $waitSeconds seconds for next run of scheduler\n");
        sleep($waitSeconds);
        $this->triggerCronCall();
    }

    /**
     * Clear schedules logs with an optional time offset.
     *
     * @return void
     */
    public function actionClearLogs()
    {
        if($this->all) {
            Plugin::$plugin->getLogs()->deleteAllLogs();
            $this->stdout("Deleted all logs \n", Console::FG_GREEN);

            return;
        }

        if(Plugin::getInstance()->getSettings()->logExpireAfter || $this->expire) {
            $expire = $this->expire ?: Plugin::getInstance()->getSettings()->logExpireAfter;
            $validator = new CarbonStringIntervalValidator;

            if($validator->validate($expire, $error)) {
                Plugin::$plugin->getLogs()->deleteLogsByDateCreated(
                    Carbon::now()->subtract($expire)
                );

                $interval = CarbonInterval::make($expire);
                $this->stdout("Deleted all logs older than {$interval->forHumans()} \n", Console::FG_GREEN);

                return;
            }

            $this->stderr($error .  ".\n", Console::FG_RED);

            return;
        }

        $this->stdout("Provide the expire or all option to use this command. \n", Console::FG_YELLOW);
    }

    protected function triggerCronCall(array $events = null)
    {
        $this->stdout("Running scheduler \n");
        $this->actionRun($events);
        $this->stdout("completed, sleeping... \n");

        $sec = $this->nextMinute();
        if ($sec >= 5) {
            // Use free time to get events.
            $events = Plugin::$plugin->createBuilder()
                ->build($this->force ?? false)
                ->dueEvents(Craft::$app);
        } else {
            $events = null;
        }

        sleep($sec < 5 ? $sec : $this->nextMinute());
        $this->triggerCronCall($events);
    }

    /**
     * @return int
     */
    protected function nextMinute(): int
    {
        $current = Carbon::now();
        return 60 - $current->second;
    }
}
