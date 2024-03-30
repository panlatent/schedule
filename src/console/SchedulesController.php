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
use craft\validators\HandleValidator;
use panlatent\schedule\base\ScheduleInterface;
use panlatent\schedule\BuilderEvent;
use panlatent\schedule\Plugin;
use panlatent\schedule\schedules\Console as ConsoleSchedule;
use panlatent\schedule\validators\CarbonStringIntervalValidator;
use Symfony\Component\Process\Process;
use yii\console\Controller;
use yii\helpers\Console;

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
    public ?bool $force = null;

    /**
     * @var bool Async run schedules.
     */
    public bool $async = false;

    /**
     * @var bool|null Clear all logs.
     */
    public ?bool $all = null;

    /**
     * @var string|null Expiry offset for log clearing.
     */
    public ?string $expire = null;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function options($actionID): array
    {
        $options = parent::options($actionID);
        switch ($actionID) {
            case 'run': // no break
            case 'listen':
                $options[] = 'force';
                $options[] = 'async';
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
    public function actionList(): void
    {
        $schedules = Plugin::$plugin->getSchedules();

        $i = 0;
        $ungroupedSchedules = $schedules->getSchedulesByGroupId();
        $this->stdout(Craft::t('schedule', 'Static') . ": \n", Console::FG_YELLOW);
        foreach ($ungroupedSchedules as $schedule) {
            if ($schedule->static)
            $this->stdout(Console::renderColoredString("    > #$i %c$schedule->handle\n"));
            ++$i;
        }
        $this->stdout(Craft::t('schedule', 'Ungrouped') . ": \n", Console::FG_YELLOW);
        foreach ($ungroupedSchedules as $schedule) {
            $this->stdout(Console::renderColoredString("    > #$i %c$schedule->handle\n"));
            ++$i;
        }

        foreach ($schedules->getAllGroups() as $group) {
            $this->stdout("$group->name: \n", Console::FG_YELLOW);
            foreach ($group->getSchedules() as $schedule) {
                // @var Schedule $schedule

                $this->stdout(Console::renderColoredString("    > #$i %c$schedule->handle\n"));
                ++$i;
            }
        }
    }

    /**
     * Run all schedules.
     *
     * @param BuilderEvent[]|null $events
     */
    public function actionRun(array $events = null): void
    {
        if ($events === null) {
            $events = Plugin::$plugin->createBuilder()
                ->build($this->force ?? false)
                ->dueEvents();
        }

        if (empty($events)) {
            $this->stdout("No scheduled commands are ready to run.\n");
            return;
        }

        foreach ($events as $event) {
            if (!$this->async) {
                $this->actionRunSchedule(null, $event->schedule);
            } else {
                $this->stdout("Running async schedule: " . $event->schedule->handle . "\n");
                $process = new Process([PHP_BINARY, ConsoleSchedule::CRAFT_CLI_SCRIPT, 'schedules/run-schedule', $event->schedule->uid], dirname(Craft::$app->request->getScriptFile()));
                $process->start();
            }
        }

        Craft::info("Running scheduled event total: " . count($events), __METHOD__);
    }

    /**
     * @param string|null $search
     * @param ScheduleInterface|null $schedule
     * @return int
     */
    public function actionRunSchedule(string $search = null, ScheduleInterface $schedule = null): int
    {
        if ($schedule === null) {
            if (ctype_digit($search)) {
                $schedule = Plugin::$plugin->getSchedules()->getScheduleById($search);
                $type = 'id';
            } elseif (preg_match('#^[a-zA-Z][a-zA-Z0-9_]*$#', $search)) {
                $schedule = Plugin::$plugin->getSchedules()->getScheduleByHandle($search);
                $type = 'handle';
            } else {
                $schedule = Plugin::$plugin->getSchedules()->getScheduleByUid($search);
                $type = 'uid';
            }
        }

        if (!$schedule) {
            $this->stderr("Not found schedule with $type: $search\n");
            return 1;
        }

        $info = sprintf('#%d %s[%s]', $schedule->id, $schedule->name, $schedule->handle);
        $this->stdout("Running schedule: $info ... ");
        $start = microtime(true);
        try {
            $schedule->run();
            $duration = round((microtime(true) - $start), 2);
            $this->stdout("done({$duration}s)\n");
            Craft::info("Running schedule: $info", __METHOD__);
        } catch (\Throwable $e) {
            $duration = round((microtime(true) - $start), 2);
            $this->stdout("failed({$duration}s)\n");
            $this->stderr("Error: {$e->getMessage()}\n");
            Craft::error("Running schedule: $info", __METHOD__);
            return -1;
        }

        return 0;
    }

    /**
     * Run a permanent command to call crons run command every minute
     *
     * @return void
     */
    public function actionListen(): void
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
    public function actionClearLogs(): void
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

    protected function triggerCronCall(array $events = null): void
    {
        $this->stdout("Running scheduler \n");
        $this->actionRun($events);
        $this->stdout("completed, sleeping... \n");

        $sec = $this->nextMinute();
        if ($sec >= 5) {
            // Use free time to get events.
            $events = Plugin::$plugin->createBuilder()
                ->build($this->force ?? false)
                ->dueEvents();
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
