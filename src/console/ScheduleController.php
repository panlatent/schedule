<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\console;

use Craft;
use panlatent\schedule\Plugin;
use yii\console\Controller;

/**
 * Class ScheduleController
 *
 * @package panlatent\schedule\console
 * @author Panlatent <panlatent@gmail.com>
 */
class ScheduleController extends Controller
{
    public function actionList()
    {

    }

    /**
     * Run all schedules.
     */
    public function actionRun()
    {
        $events = Plugin::$plugin->getBuilder()
            ->build()
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
}