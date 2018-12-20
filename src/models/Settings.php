<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\models;

use yii\base\Model;

/**
 * Class Settings
 *
 * @package panlatent\schedule\models
 * @author Panlatent <panlatent@gmail.com>
 */
class Settings extends Model
{
    /**
     * Callback function:
     *
     * ```php
     * function (Builder $schedule) {
     *     $schedule->command('list')->everyFiveMinutes();
     * }
     * ```php
     *
     * @var callable|null Use a callback function to register some schedule.
     */
    public $scripts;
}