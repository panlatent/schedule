<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\helpers;

use Craft;
use Panlatent\CronExpressionDescriptor\ExpressionDescriptor;

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
    public static function toCronExpression(mixed $value): ?string
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

        return implode(' ', [$minute, $hour, $day, $month, $week]);
    }

    /**
     * @param array|string $expression
     * @return string
     */
    public static function toDescription(array|string $expression): string
    {
        $expression = self::toCronExpression($expression);

        $targetLanguage = Craft::$app->getTargetLanguage();
        if ($targetLanguage == 'zh') {
            $targetLanguage = 'zh-Hans';
        }

        $descriptor = new ExpressionDescriptor($expression, $targetLanguage);

        return $descriptor->getDescription();
    }
}