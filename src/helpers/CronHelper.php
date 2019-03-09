<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\helpers;

use Craft;

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
            $value = explode(' ', $value);
        }

        if (!is_array($value)) {
            return null;
        }

        $value = array_map(function($item) {
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
     * @return string
     */
    public static function toDescription($expression): string
    {
        $expression = self::toCronExpression($expression);
        switch ($expression) {
            case '* * * * * *':
                $description = 'Every Minute';
                break;
            case '0 * * * * *':
                $description = 'Hourly';
                break;
            case '0 0 * * * *':
                $description = 'Daily';
                break;
            case '0 0 1 * * *':
                $description = 'Monthly';
                break;
            case '0 0 1 1 * *':
                $description = 'Yearly';
                break;
            case '0 0 * * 0 *':
                $description = 'Weekly';
                break;
        }

        if (!empty($description)) {
            return Craft::t('schedule', $description);
        }

        list($minute, $hour, $day, $month, $week, ) = explode(' ', $expression);
        $cron = compact('minute', 'hour', 'day', 'month', 'week');

        $cron = array_filter($cron, function($value) {
            return $value !== '*';
        });

        $segments = [];
        foreach ($cron as $unit => $value) {
            $description = '';
            $params = [];

            $unitLabel = Craft::t('schedule', ucfirst($unit));
            $unitsLabel = Craft::t('schedule', $unit . 's');
            $params['unit'] = $unitLabel;
            $params['units'] = $unitsLabel;

            if (ctype_alnum($value)) {
                $description = '{value} {unit}';

                $params['value'] = self::_getNumLabel($value);
            } elseif (strpos($value, '/') !== false) {
                list($start, $interval) = explode('/', $value, 2);
                if ($start == '*') {
                    $description = 'every {value} {unit}';
                    $params['value'] = $interval;
                } else {
                    $description = 'every {value} {unit}(from {start} {unit})';
                    $params['value'] = $interval;
                    $params['start'] =  self::_getNumLabel($start);
                }
            } elseif (strpos($value, '-') !== false) {
                $description = '{from} - {to} {units}';
                list($from, $to) = explode('-', $value, 2);
                $params['from'] = self::_getNumLabel($from);
                $params['to'] = self::_getNumLabel($to);
            } elseif (strpos($value, ',') !== false) {
                $items = explode(',', $value);
                foreach ($items as $index => $item) {
                    $description .= self::_getNumLabel($item) . ',';
                }

                $description = rtrim($description, ',' ). ' ' . Craft::t('schedule', '{units}');
            }

            $segments[] = Craft::t('schedule', $description, $params);
        }

        if (count($segments) == 1) {
            return reset($segments);
        }

        return implode(', ', $segments);
    }

    /**
     * @param int $value
     * @return string
     */
    private static function _getNumLabel(int $value)
    {
        if ($value > 3 || $value < 1) {
            return Craft::t('schedule', '{num}th', ['num' => $value]);
        }

        return Craft::t('schedule', [1 => '{num}st', 2 => '{num}nd', 3 => '{num}rd'][$value], ['num' => $value]);
    }
}