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
 * Class ScheduleCriteria
 *
 * @package panlatent\schedule\models
 * @author Panlatent <panlatent@gmail.com>
 */
class ScheduleCriteria extends Model
{
    /**
     * @var string|null
     */
    public $search;

    /**
     * @var bool|null
     */
    public $hasLogs;

    /**
     * @var bool|null
     */
    public $enabledLog;

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