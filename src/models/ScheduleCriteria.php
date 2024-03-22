<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
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
    public ?string $search = null;

    /**
     * @var bool|null
     */
    public ?bool $hasLogs = null;

    /**
     * @var bool|null
     */
    public ?bool $enabledLog = null;

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