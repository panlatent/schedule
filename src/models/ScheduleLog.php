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
}