<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2019 panlatent@gmail.com
 */

namespace panlatent\schedule\models;

use craft\base\Model;

/**
 * Class ScheduleLog
 *
 * @package panlatent\schedule\models
 * @property-read int $duration
 * @author Panlatent <panlatent@gmail.com>
 */
class ScheduleLog extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var int|null
     */
    public $id;

    /**
     * @var int|null
     */
    public $scheduleId;

    /**
     * @var string|null
     */
    public $status;

    /**
     * @var string|null
     */
    public $reason;

    /**
     * @var int|null
     */
    public $startTime;

    /**
     * @var int|null
     */
    public $endTime;

    /**
     * @var string|null
     */
    public $output;

    /**
     * @var int|null
     */
    public $sortOrder;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [];
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
}