<?php

namespace panlatent\schedule\models;

use craft\base\Model;
use panlatent\craft\actions\abstract\ActionInterface;
use panlatent\schedule\base\TimerInterface;

/**
 * @property-read ScheduleGroup $group
 * @property-read TimerInterface[] $timers
 * @property-read ActionInterface[] $actions
 * @since 1.0.0
 */
class Schedule extends Model
{
    /**
     * @var int|null
     */
    public ?int $groupId = null;

    /**
     * @var string|null
     */
    public ?string $name = null;

    /**
     * @var string|null
     */
    public ?string $handle = null;



    /**
     * @var string|null
     */
    public ?string $description = null;

    /**
     * Static schedules are stored in the project config.
     * @var bool
     */
    public bool $static = false;

    public ?ActionInterface $action = null;

    /**
     * @var bool
     */
    public bool $enabled = true;

    /**
     * @var bool|null
     */
    public ?bool $enabledLog = null;

    /**
     * @var int|null
     */
    public ?int $lastStartedTime = null;

    /**
     * @var int|null
     */
    public ?int $lastFinishedTime = null;

    /**
     * @var string|null
     */
    public ?string $lastStatus = null;

    /**
     * @var int|null
     */
    public ?int $sortOrder = null;

    /**
     * @return TimerInterface[]
     */
    public function getTimers(): array
    {
        return [];
    }

    /**
     * @return array
     * @todo
     */
    public function getConditions(): array
    {
        return [];
    }

    public function canRun(): bool
    {
        return true;
    }

    public function run(): void
    {
        $task = new ScheduleTask();

        $context = new Context(\Craft::$app->getLog()->getLogger());
        $this->action->execute($context);

        if ($context->getOutput()->canStored()) {
            // Store ...
        }

        $errors = $context->getErrors();
        if (empty($errors)) {
            $this->onFailure();
            return;
        }

        $this->onSuccess();
    }

    public function onSuccess(): void
    {

    }

    public function onFailure(): void
    {

    }
}