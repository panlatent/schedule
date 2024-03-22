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
    public array|int|null $scheduleId = null;

    /**
     * @var Schedule|string|null
     */
    public Schedule|string|null $schedule = null;

    /**
     * @var string|null
     */
    public ?string $sortOrder = null;

    /**
     * @var int|null
     */
    public ?int $offset = null;

    /**
     * @var int|null
     */
    public ?int $limit = null;
}