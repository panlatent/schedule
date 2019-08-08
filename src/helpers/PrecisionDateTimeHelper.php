<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2019 panlatent@gmail.com
 */

namespace panlatent\schedule\helpers;

use Craft;
use DateTime;
use DateTimeZone;

/**
 * Class DateTimeHelper
 *
 * @package panlatent\schedule\helpers
 * @author Panlatent <panlatent@gmail.com>
 */
class PrecisionDateTimeHelper
{
    // Static Methods
    // =========================================================================

    /**
     * @return int
     */
    public static function time(): int
    {
        return round(microtime(true) * 1000);
    }

    /**
     * @param string|int $value
     * @param bool $setToSystemTimeZone
     * @return DateTime
     */
    public static function toDateTime($value, bool $setToSystemTimeZone = true): DateTime
    {
        $timestamp = substr($value, 0, -3);
        $datetime = new DateTime("@{$timestamp}");

        if ($setToSystemTimeZone) {
            $datetime->setTimezone(new DateTimeZone(Craft::$app->getTimeZone()));
        }

        return $datetime;
    }

    /**
     * @param string $format
     * @param mixed $value
     * @param bool $setToSystemTimeZone
     * @return string
     */
    public static function format(string $format, $value, bool $setToSystemTimeZone = true): string
    {
        $timestamp = substr($value, 0, -3);
        $microsecond = substr($value, -3);

        $datetime = new DateTime("@{$timestamp}");
        if ($setToSystemTimeZone) {
            $datetime->setTimezone(new DateTimeZone(Craft::$app->getTimeZone()));
        }

        return $datetime->format(preg_replace('#s#', 's.' . $microsecond, $format));
    }
}