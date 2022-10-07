<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\schedules;

use Closure;
use Craft;
use craft\queue\QueueInterface;
use panlatent\schedule\base\Schedule;
use panlatent\schedule\errors\ScheduleException;
use panlatent\schedule\helpers\ClassHelper;
use ReflectionClass;
use yii\queue\JobInterface;

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
    public function getSettingsHtml(): ?string
    {
        $componentSuggestions = [
            [
                'label' => '',
                'data' => $this->_getComponentSuggestions(),
            ]
        ];

        $jobClassSuggestions = [
            [
                'label' => '',
                'data' => $this->_getJobClassSuggestions(),
            ]
        ];

        return Craft::$app->getView()->renderTemplate('schedule/_components/schedules/Queue/settings', [
            'schedule' => $this,
            'componentSuggestions' => $componentSuggestions,
            'jobClassSuggestions' => $jobClassSuggestions,
        ]);
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function execute(int $logId = null): bool
    {
        $queue = Craft::$app->get($this->componentId, false);
        if ($queue === null) {
            throw new ScheduleException("No queue component exists with the ID: {$this->componentId}");
        }

        if (!$queue instanceof \yii\queue\Queue && !$queue instanceof QueueInterface) {
            throw new ScheduleException('Invalid queue component');
        }

        /** @var \yii\queue\Queue $queue */
        $job = new $this->jobClass();
        $queue->push($job);

        Craft::info("Queue Schedule push a job: {$this->jobClass} to {$this->componentId} component.", __METHOD__);

        return true;
    }

    // Private Methods
    // =========================================================================

    /**
     * @return array
     */
    private function _getComponentSuggestions(): array
    {
        $suggestions = [];

        $components = Craft::$app->getComponents();
        ksort($components);

        foreach ($components as $id => $component) {
            $hint = '';
            if (is_object($component) && !$component instanceof Closure) {
                $hint = get_class($component);
            } elseif (!is_callable($component) && is_array($component) && !empty($component['class'])) {
                $hint = $component['class'];
            }

            $suggestions[] = [
                'name' => $id,
                'hint' => $hint,
            ];
        }

        return $suggestions;
    }

    /**
     * @return array
     */
    private function _getJobClassSuggestions(): array
    {
        $suggestions = [];
        foreach (ClassHelper::findClasses() as $class) {
            if (is_subclass_of($class, JobInterface::class)) {
                $reflection = new ReflectionClass($class);
                if (!$reflection->isInstantiable()) {
                    continue;
                }

                $suggestions[] = [
                    'name' => $class,
                    'hint' => ClassHelper::getPhpDocSummary($reflection->getDocComment()),
                ];
            }
        }

        return $suggestions;
    }
}