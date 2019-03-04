<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\timers;

use craft\base\MissingComponentTrait;
use panlatent\schedule\base\Timer;

/**
 * Class MissingTimer
 *
 * @package panlatent\schedule\timers
 * @author Panlatent <panlatent@gmail.com>
 */
class MissingTimer extends Timer
{
    use MissingComponentTrait;
}