<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2019 panlatent@gmail.com
 */

namespace panlatent\schedule\models;

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