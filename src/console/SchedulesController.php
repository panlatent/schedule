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
use panlatent\schedule\base\ScheduleInterface;
use panlatent\schedule\BuilderEvent;
use panlatent\schedule\models\Schedule;
use panlatent\schedule\Plugin;
use panlatent\schedule\Scheduler;
use panlatent\schedule\schedules\Console as ConsoleSchedule;
use panlatent\schedule\validators\CarbonStringIntervalValidator;
use Symfony\Component\Process\Process;
use Throwable;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\console\widgets\Table;
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

    /**
     * @var string
     */
    public string $expireDefault = '7days';

    /**
     * @var bool True will automatically clear logs.
     */
    public bool $withClearLogs = false;

    public bool $check = false;

    // Public Methods
    // =========================================================================


    /**
     * @inheritdoc
     */
    public function options($actionID): array
    {
        $options = parent::options($actionID);
        switch ($actionID) {
            case 'run-schedule':
                $options[] = 'check';
            case 'run':
                $options[] = 'force';
                $options[] = 'async';
                break;
            case 'listen':
                $options[] = 'force';
                $options[] = 'async';
                $options[] = 'withClearLogs';
                $options[] = 'expire';
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
        $schedules = Plugin::getInstance()->schedules;

        $i = 1;
        $rows = [];

        $ungroupedSchedules = $schedules->getSchedulesByGroupId();
        foreach ($ungroupedSchedules as $schedule) {
            if ($schedule->static) {
                $rows[] = [$i++, $schedule->id, Console::ansiFormat('Static', [Console::FG_PURPLE]), $schedule->name, $schedule->handle];
            }
        }
        foreach ($ungroupedSchedules as $schedule) {
            if (!$schedule->static) {
                $rows[] = [$i++, $schedule->id, Console::ansiFormat('Ungrouped', [Console::FG_YELLOW]), $schedule->name, $schedule->handle];
            }
        }
        foreach ($schedules->getAllGroups() as $group) {
            $this->stdout("$group->name: \n", Console::FG_YELLOW);
            foreach ($group->getSchedules() as $schedule) {
                $rows[] = [$i++, $schedule->id, $group->name, $schedule->name, $schedule->handle];
            }
        }

        echo Table::widget([
            'headers' => ['No.', 'ID', 'Group', 'Name', 'Handle'],
            'rows' => $rows,
        ]);
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
     * @param Schedule|null $schedule
     * @return int
     */
    public function actionRunSchedule(string $search = null, Schedule $schedule = null): int
    {
        if ($this->check) {
            return ExitCode::OK;
        }

        $schedules = Plugin::getInstance()->schedules;

        if ($schedule === null) {
            if (ctype_digit($search)) {
                $schedule = $schedules->getScheduleById($search);
                $type = 'id';
            } elseif (preg_match('#^[a-zA-Z][a-zA-Z0-9_]*$#', $search)) {
                $schedule = $schedules->getScheduleByHandle($search);
                $type = 'handle';
            } else {
                $schedule = $schedules->getScheduleByUid($search);
                $type = 'uid';
            }
            if (!$schedule) {
                $this->stderr("Not found schedule with $type: $search\n");
                return 1;
            }
        }

        $info = sprintf('#%d %s[%s]', $schedule->id, $schedule->name, $schedule->handle);
        $this->stdout("Running schedule: $info ... ");

        $scheduler = new Scheduler();

        $start = microtime(true);
        try {
            if (!$scheduler->runSchedule($schedule)) {
                throw new \RuntimeException("Failed to run schedule");
            }
            $duration = round((microtime(true) - $start), 2);
            $this->stdout("done({$duration}s)\n");
            Craft::info("Running schedule: $info", __METHOD__);
        } catch (Throwable $e) {
            $duration = round((microtime(true) - $start), 2);
            $this->stdout("failed({$duration}s)\n");
            $this->stderr("Error: {$e->getMessage()}\n");
            Craft::error("Running schedule: $info", __METHOD__);
            return -1;
        }

        return ExitCode::OK;
    }

    /**
     * Run a permanent command to call crons run command every minute
     */
    public function actionListen(): never
    {
        if ($this->force === null) {
            $this->force = true;
        }

        if (!$this->force) {
            $this->stdout("(!) Notice: Force option is disable, all schedules updates will not be synchronized.\n");
        }

        $scheduler = new Scheduler();
        $scheduler->listen(function($output) {
            $this->stdout($output);
           // $this->stdout("Waiting $waitSeconds seconds for next run of scheduler\n");
        });
//        $waitSeconds = $this->nextMinute();
//        $this->stdout("Waiting $waitSeconds seconds for next run of scheduler\n");
//        sleep($waitSeconds);
//        $this->triggerCronCall();
    }

    /**
     * Clear schedules logs with an optional time offset.
     *
     * If expire cannot be obtained from any settings, it defaults to `expireDefault`(7 days).
     *
     * @param string|null $expire Default: 7day
     */
    public function actionClearLogs(string $expire = null): void
    {
        if ($this->all) {
            Plugin::$plugin->getLogs()->deleteAllLogs();
            $this->stdout("Deleted all logs \n", Console::FG_GREEN);
            return;
        }

        if ($expire === null) {
            $expire = $this->expire ?: Plugin::getInstance()->getSettings()->logExpireAfter;
            if ($expire === null) {
                $expire = $this->expireDefault;
            }
        }

        $validator = new CarbonStringIntervalValidator();
        if(!$validator->validate($expire, $error)) {
            $this->stderr($error .  ".\n", Console::FG_RED);
            return;
        }

        Plugin::$plugin->getLogs()->deleteLogsByDateCreated(
            Carbon::now()->subtract($expire)
        );
        $interval = CarbonInterval::make($expire);
        $this->stdout("Deleted all logs older than {$interval->forHumans()} \n", Console::FG_GREEN);
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

            if ($this->withClearLogs) {
                $this->actionClearLogs();
            }
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
