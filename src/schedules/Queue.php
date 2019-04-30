<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\schedules;

use Craft;
use craft\queue\QueueInterface;
use panlatent\schedule\base\Schedule;
use panlatent\schedule\errors\ScheduleException;

/**
 * Class JobSchedule
 *
 * @package panlatent\schedule\schedules
 * @author Panlatent <panlatent@gmail.com>
 */
class Queue extends Schedule
{
    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('schedule', 'Queue');
    }

    /**
     * @inheritdoc
     */
    public static function isRunnable(): bool
    {
        return true;
    }

    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public $componentId = 'queue';

    /**
     * @var string|null
     */
    public $jobClass;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('schedule/_components/schedules/Queue', [
            'schedule' => $this,
        ]);
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function execute(int $logId = null): bool
    {
        $queue = Craft::$app->get($this->componentId);
        if (!$queue || $queue instanceof \yii\queue\Queue || $queue instanceof QueueInterface) {
            throw new ScheduleException('Invalid queue component');
        }
        Craft::info("x__x");

        /** @var \yii\queue\Queue $queue */
        $job = new $this->jobClass();
        $queue->push($job);

        Craft::info("Queue Schedule push a job: {$this->jobClass} to {$this->componentId} component.", __METHOD__);

        return true;
    }
}