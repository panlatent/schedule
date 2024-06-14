<?php

namespace panlatent\schedule\models;

use Craft;
use craft\base\Model;
use DateTime;
use panlatent\craft\actions\abstract\ActionInterface;
use panlatent\schedule\base\TimerInterface;
use panlatent\schedule\log\LogAdapter;
use Psr\Log\LoggerInterface;

/**
 * @property-read ScheduleGroup $group
 * @property-read ScheduleInfo $info
 * @since 1.0.0
 */
class Schedule extends Model
{
    public ?int $id = null;
    public ?int $groupId = null;
    public ?string $name = null;
    public ?string $handle = null;
    public ?string $description = null;

    /**
     * Static schedules are stored in the project config.
     * @var bool
     */
    public bool $static = false;

    public ?ActionInterface $action = null;

    public ?TimerInterface $timer = null;

    /**
     * @var bool
     */
    public bool $enabled = true;

    /**
     * @var bool|null
     */
    public ?bool $enabledLog = null;

//    public $timeout;

    /**
     * @var int|null
     */
    public ?int $sortOrder = null;

    public ?string $uid = null;

    public ?DateTime $dateCreated = null;

    public ?DateTime $dateUpdated = null;

    private ?ScheduleInfo $_info = null;

    /**
     * @return array
     * @todo
     */
    public function getConditions(): array
    {
        return [];
    }

    public function getInfo(): ScheduleInfo
    {
        if ($this->_info === null) {
            $this->_info = new ScheduleInfo();
        }
        return $this->_info;
    }

    public function canRun(): bool
    {
        return true;
    }

    public function run(): void
    {
        $task = new ScheduleTask();

        $context = new Context($this->getLogger());

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

    protected function getLogger(): LoggerInterface
    {
        return new LogAdapter(Craft::$app->getLog()->getLogger(), 'schedule');
    }
}