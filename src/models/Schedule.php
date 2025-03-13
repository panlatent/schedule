<?php

namespace panlatent\schedule\models;

use Craft;
use craft\base\Model;
use craft\validators\HandleValidator;
use craft\validators\UniqueValidator;
use DateTime;
use Panlatent\Action\ActionInterface;
use panlatent\schedule\base\TimerInterface;
use panlatent\schedule\builder\Buidler;
use panlatent\schedule\log\LogAdapter;
use panlatent\schedule\Plugin;
use panlatent\schedule\records\Schedule as ScheduleRecord;
use Psr\Log\LoggerInterface;
use function Arrayy\array_first;

/**
 * @property-read ScheduleGroup $group
 * @property-read array $conditions
 * @property-read LoggerInterface $logger
 * @property TimerInterface $timer
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

//    public ?TimerInterface $timer = null;

    public ?int $timeout = null;

    public ?int $retry = null;

    public $onSuccess = null;

    public $onFailed = null;

    /**
     * @var bool
     */
    public bool $enabled = true;

//    /**
//     * @var bool|null
//     */
    //public ?bool $enabledLog = null;


    /**
     * @var int|null
     */
    public ?int $sortOrder = null;

    public ?string $uid = null;

    public ?DateTime $dateCreated = null;

    public ?DateTime $dateUpdated = null;

    private ?ScheduleInfo $_info = null;

    public static function make()
    {
        return new Buidler();
    }

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

    private ?TimerInterface $_timer = null;

    public function getTimer(): TimerInterface
    {
        if ($this->_timer === null) {
            $this->_timer = Plugin::getInstance()->timers->getTimerByScheduleId($this->id);
        }
        return $this->_timer;
    }

    public function setTimer(TimerInterface $timer): void
    {
        $this->_timer = $timer;
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

    protected function defineRules(): array
    {
        return [
            [['name', 'handle'], 'required'],
            [['id', 'groupId'], 'integer'],
            [['name', 'handle', 'description'], 'string'],
            [['handle'], UniqueValidator::class, 'targetClass' => ScheduleRecord::class, 'targetAttribute' => 'handle'],
            [['handle'], HandleValidator::class],
            [['static'], 'boolean'],
            [['action'], function($attribute) {if (!$this->$attribute->validate()) {
                $this->addError($attribute, array_first($this->$attribute->getFirstErrors()));
            }}],

            [['timer'], function($attribute) {
                if (!$this->$attribute->validate()) {
                    $errors = $this->$attribute->getFirstErrors();
                    $this->addError($attribute, reset($errors));
                }
            }],
        ];
    }
}