<?php

/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\console;

use Craft;
use Carbon\Carbon;
use panlatent\schedule\base\Schedule;
use panlatent\schedule\Plugin;
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
     * @var bool|null Force flush schedule repository.
     */
    public $force;

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
     */
    public function actionRun()
    {
        $events = Plugin::$plugin->getBuilder()
            ->build($this->force)
            ->dueEvents(Craft::$app);

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

        $waitSeconds = $this->nextMinute();
        $this->stdout("Waiting $waitSeconds seconds for next run of scheduler\n");
        sleep($waitSeconds);
        $this->triggerCronCall();
    }

    protected function triggerCronCall()
    {
        $this->stdout("Running scheduler \n");
        $this->actionRun();
        $this->stdout('completed, sleeping... \n');
        sleep($this->nextMinute());
        $this->triggerCronCall();
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
