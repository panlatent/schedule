<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
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