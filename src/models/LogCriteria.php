<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\models;

use panlatent\schedule\base\Schedule;
use yii\base\Model;

/**
 * Class LogCriteria
 *
 * @package panlatent\schedule\models
 * @author Panlatent <panlatent@gmail.com>
 */
class LogCriteria extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var int[]|int|null
     */
    public $scheduleId;

    /**
     * @var Schedule|string|null
     */
    public $schedule;

    /**
     * @var string|null
     */
    public $sortOrder;

    /**
     * @var int|null
     */
    public $offset;

    /**
     * @var int|null
     */
    public $limit;
}