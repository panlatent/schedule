<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\models;

use craft\base\Model;
use DateTime;
use panlatent\schedule\base\ScheduleInterface;
use panlatent\schedule\helpers\PrecisionDateTimeHelper;
use panlatent\schedule\Plugin;
use yii\base\InvalidConfigException;

/**
 * Class ScheduleLog
 *
 * @package panlatent\schedule\models
 * @property-read ScheduleInterface $schedule
 * @property-read int $duration
 * @property-read DateTime|null $startDate
 * @author Panlatent <panlatent@gmail.com>
 */
class ScheduleLog extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var int|null
     */
    public ?int $id = null;

    /**
     * @var int|null
     */
    public ?int $scheduleId = null;

    /**
     * @var string|null
     */
    public ?string $status = null;

    /**
     * @var string|null
     */
    public ?string $reason = null;

    /**
     * @var int|null
     */
    public ?int $startTime = null;

    /**
     * @var int|null
     */
    public ?int $endTime = null;

    /**
     * @var string|null
     */
    public ?string $output = null;

    /**
     * @var int|null
     */
    public ?int $sortOrder = null;

    /**
     * @var ScheduleInterface|null
     */
    private ?ScheduleInterface $_schedule = null;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function attributes(): array
    {
        $attributes = parent::attributes();
        $attributes[] = 'schedule';
        $attributes[] = 'duration';
        $attributes[] = 'startDate';

        return $attributes;
    }

    /**
     * @return ScheduleInterface
     */
    public function getSchedule(): ScheduleInterface
    {
        if ($this->_schedule !== null) {
            return $this->_schedule;
        }

        if ($this->scheduleId === null) {
            throw new InvalidConfigException('Schedule log is missing its schedule ID');
        }

        $this->_schedule = Plugin::getInstance()->getSchedules()->getScheduleById($this->scheduleId);
        if ($this->_schedule === null) {
            throw new InvalidConfigException('Invalid schedule ID: ' . $this->scheduleId);
        }

        return $this->_schedule;
    }

    /**
     * @return int Duration(ms)
     */
    public function getDuration(): int
    {
        if (!$this->startTime || !$this->endTime) {
            return 0;
        }

        return $this->endTime - $this->startTime;
    }

    /**
     * @return DateTime|null
     */
    public function getStartDate(): ?DateTime
    {
        if ($this->startTime === null) {
            return null;
        }

        return PrecisionDateTimeHelper::toDateTime($this->startTime);
    }

    /**
     * @return string
     */
    public function getOutputHtml(): string
    {
        return $this->getSchedule()->renderLogContent($this);
    }
}