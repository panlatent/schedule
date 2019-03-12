<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\helpers;

use panlatent\schedule\models\CronExpression;

/**
 * Class CronHelper
 *
 * @package panlatent\schedule\helpers
 * @author Panlatent <panlatent@gmail.com>
 */
class CronHelper
{
    /**
     * @param mixed $value
     * @return string|null
     */
    public static function toCronExpression($value)
    {
        if (is_string($value)) {
            $value = preg_split('#\s+#', trim($value));
        }

        if (!is_array($value)) {
            return null;
        }

        $value = array_map(function ($item) {
            if ($item === null || $item === '') {
                return '*';
            }
            return $item;
        }, $value);

        $minute = $value['minute'] ?? $value[0] ?? '*';
        $hour = $value['hour'] ?? $value[1] ?? '*';
        $day = $value['day'] ?? $value[2] ?? '*';
        $month = $value['month'] ?? $value[3] ?? '*';
        $week = $value['week'] ?? $value[4] ?? '*';

        return implode(' ', [$minute, $hour, $day, $month, $week, '*']);
    }

    /**
     * @param array|string $expression
     * @param bool $use24HourTimeFormat
     * @return string
     */
    public static function toDescription($expression, bool $use24HourTimeFormat = false): string
    {
        $expression = self::toCronExpression($expression);

        $descriptor = new CronExpression([
            'expression' => $expression,
            'use24HourTimeFormat' => $use24HourTimeFormat
        ]);

        return $descriptor->getDescription();
    }
}