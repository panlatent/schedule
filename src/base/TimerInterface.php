<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\base;

interface TimerInterface
{
    public function getMinute(): string;

    public function getHour(): string;

    public function getDay(): string;

    public function getMonth(): string;

    public function getWeek(): string;
}