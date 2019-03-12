<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\models;

use Craft;
use DateTime;
use IntlDateFormatter;
use yii\base\BaseObject;

/**
 * Class CronExpression
 *
 * @package panlatent\schedule\models
 * @property-read string $description
 * @property-read string $timeOfDayDescription
 * @property-read string $minutesDescription
 * @property-read string $hoursDescription
 * @property-read string $dayOfWeekDescription
 * @property-read string $monthDescription
 * @property-read string $dayOfMonthDescription
 * @property-read string $yearDescription
 * @author Panlatent <panlatent@gmail.com>
 */
class CronExpression extends BaseObject
{
    // Properties
    // =========================================================================

    /**
     * @var array|string|null
     */
    public $expression;

    /**
     * @var bool
     */
    public $use24HourTimeFormat = false;

    /**
     * @var string[]
     */
    public $defaultSpecialCharacters = ['/', '-', ',', '*'];

    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        list($minute, $hour, $day, $month, $week, $year) = array_pad(explode(' ', $this->expression), 6, '*');
        $this->expression = compact('minute', 'hour', 'day', 'month', 'week', 'year');
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        $timeSegment = $this->getTimeOfDayDescription();
        $dayOfMonthDesc = $this->getDayOfMonthDescription();
        $monthDesc = $this->getMonthDescription();
        $dayOfWeekDesc = $this->getDayOfWeekDescription();
        $yearDesc = $this->getYearDescription();

        $description = $timeSegment . $dayOfMonthDesc . $dayOfWeekDesc . $monthDesc . $yearDesc;

        $description = str_replace([
            Craft::t('schedule', 'ComaEveryMinute'),
            Craft::t('schedule', 'ComaEveryHour'),
            Craft::t('schedule', 'ComaEveryDay'),
            ', ' . Craft::t('schedule', 'EveryMinute'),
            ', ' . Craft::t('schedule', 'EveryHour'),
            ', ' . Craft::t('schedule', 'EveryDay'),
            ', ' . Craft::t('schedule', 'EveryYear'),
        ], '', $description);

        return rtrim($description, ', ');
    }

    /**
     * @return string
     */
    public function getTimeOfDayDescription(): string
    {
        $minute = $this->expression['minute'];
        $hour = $this->expression['hour'];

        if (!$this->contains($minute) && !$this->contains($hour)) {
            $description = Craft::t('schedule', 'AtSpace') . self::formatTime($hour, $minute);
        } elseif ($this->contains($minute, '-') && !$this->contains($minute, ',') && !$this->contains($hour)) {
            //minute range in single hour (i.e. 0-10 11)
            $minuteParts = explode('-', $minute);
            $description = Craft::t('schedule', 'EveryMinuteBetween{0}And{1}', [
                '0' => self::formatTime($hour, $minuteParts[0]),
                '1' => self::formatTime($hour, $minuteParts[2]),
            ]);
        } elseif ($this->contains($hour, ',') && !$this->contains($hour, '-') && !$this->contains($minute)) {
            //hours list with single minute (o.e. 30 6,14,16)
            $hourParts = explode(',', $hour);
            $description = Craft::t('schedule', 'At');
            for ($i = 0; $i < count($hourParts); $i++) {
                $description .= ' ' . self::formatTime($hourParts[$i], $minute);
                if ($i < (count($hourParts) - 2)) {
                    $description .= ',';
                }

                if ($i == count($hourParts) - 2) {
                    $description .= Craft::t('schedule', "SpaceAnd");
                }
            }
        } else {
            //default time description
            $minutesDescription = $this->getMinutesDescription();
            $hoursDescription = $this->getHoursDescription();

            $description = $minutesDescription;

            if (strlen($description) > 0 && strlen($hoursDescription) > 0) {
                $description .= ', ';
            }

            $description .= $hoursDescription;
        }

        return $description;
    }

    /**
     * @return string
     */
    public function getMinutesDescription(): string
    {
        $description = $this->getSegmentDescription(
            $this->expression['minute'],
            Craft::t('schedule', 'EveryMinute'),
            function ($s) {
                return $s;
            },
            function ($s) {
                return Craft::t('schedule', 'Every{0}Minutes', ['0' => $s]);
            },
            function () {
                return Craft::t('schedule', 'Minutes{0}Through{1}PastTheHour');
            },
            function ($s) {
                if (ctype_alnum($s)) {
                    return $s == "0" ? '' : Craft::t('schedule', 'At{0}MinutesPastTheHour');
                } else {
                    return Craft::t('schedule', 'At{0}MinutesPastTheHour');
                }
            },
            function () {
                return Craft::t('schedule', 'Coma{0}Through{1}');
            }
        );

        return $description;
    }

    /**
     * @return string
     */
    public function getHoursDescription(): string
    {
        $expression = $this->expression['hour'];
        $description = $this->getSegmentDescription($expression,
            Craft::t('schedule', 'EveryHour'),
            function ($s) {
                return $this->formatTime($s, '0');
            },
            function ($s) {
                return Craft::t('schedule', 'Every{0}Hours', ['0' => $s]);
            },
            function () {
                return Craft::t('schedule', 'Between{0}And{1}');
            },
            function () {
                return Craft::t('schedule', 'At{0}');
            },
            function () {
                return Craft::t('schedule', 'Coma{0}Through{1}');
            }
        );

        return $description;
    }

    /**
     * @return string
     */
    public function getDayOfWeekDescription(): string
    {
        $description = null;

        if ($this->expression['week'] == "*") {
            // DOW is specified as * so we will not generate a description and defer to DOM part.
            // Otherwise, we could get a contradiction like "on day 1 of the month, every day"
            // or a dupe description like "every day, every day".
            $description = '';
        } else {
            $description = $this->getSegmentDescription($this->expression['week'],
                Craft::t('schedule', 'ComaEveryDay'),
                function ($s) {
                    $exp = ($pos = strpos($s, '#')) !== false ? substr($s, 0, $pos) . substr($s, $pos + 1)
                        : ((strpos($s, 'L') !== false) ? str_replace('L', '', $s) : $s);

                    if (extension_loaded('intl')) {
                        $fmt = new IntlDateFormatter(Craft::$app ? Craft::$app->getTargetLanguage() : 'en',
                            IntlDateFormatter::FULL, IntlDateFormatter::FULL, null, IntlDateFormatter::GREGORIAN, 'EEEE');
                        return $fmt->format((new DateTime("$exp week"))->getTimestamp());
                    }

                    return $exp;
                },
                function ($s) {
                    return Craft::t('schedule', 'ComaEvery{0}DaysOfTheWeek', ['0' => $s]);
                },
                function () {
                    return Craft::t('schedule', 'Coma{0}Through{1}');
                },
                function ($s) {
                    $format = null;
                    if (($pos = strpos($s, '#')) !== false) {
                        $dayOfWeekOfMonthNumber = substr($s, $pos + 1);
                        $dayOfWeekOfMonthDescription = null;
                        switch ($dayOfWeekOfMonthNumber) {
                            case "1":
                                $dayOfWeekOfMonthDescription = Craft::t('schedule', "First");
                                break;
                            case "2":
                                $dayOfWeekOfMonthDescription = Craft::t('schedule', "Second");
                                break;
                            case "3":
                                $dayOfWeekOfMonthDescription = Craft::t('schedule', "Third");
                                break;
                            case "4":
                                $dayOfWeekOfMonthDescription = Craft::t('schedule', "Fourth");
                                break;
                            case "5":
                                $dayOfWeekOfMonthDescription = Craft::t('schedule', 'Fifth');
                                break;
                        }
                        $format = Craft::t('schedule', 'ComaOnThe') . $dayOfWeekOfMonthDescription . Craft::t('schedule', 'Space{0}OfTheMonth');
                    } elseif (strpos($s, 'L') !== false) {
                        $format = Craft::t('schedule', "ComaOnTheLast{0}OfTheMonth");
                    } else {
                        $format = Craft::t('schedule', "ComaOnlyOn{0}");
                    }

                    return $format;
                },
                function () {
                    return Craft::t('schedule', "Coma{0}Through{1}");
                }
            );
        }

        return $description;
    }

    /**
     * @return string
     */
    public function getMonthDescription(): string
    {
        $description = $this->getSegmentDescription($this->expression['month'],
            '',
            function ($s) {
                $datetime = new DateTime("$s month 1 day");
                if (extension_loaded('intl')) {
                    $fmt = new IntlDateFormatter(Craft::$app ? Craft::$app->getTargetLanguage() : 'en', IntlDateFormatter::FULL, IntlDateFormatter::FULL, null, IntlDateFormatter::GREGORIAN, 'LLLL');
                    return $fmt->format($datetime->getTimestamp());
                }

                return $datetime->format('F');
            },
            function ($s) {
                return Craft::t('schedule', 'ComaEvery{0}Months', ['0' => $s]);
            },
            function () {
                return Craft::t('schedule', 'Coma{0}Through{1}');
            },
            function () {
                return Craft::t('schedule', 'ComaOnlyIn{0}');
            },
            function () {
                return Craft::t('schedule', 'Coma{0}Through{1}');
            }
        );

        return $description;
    }

    /**
     * @return string
     */
    public function getDayOfMonthDescription(): string
    {
        $description = null;
        $expression = $this->expression['day'];

        switch ($expression) {
            case 'L':
                $description = Craft::t('schedule', 'ComaOnTheLastDayOfTheMonth');
                break;
            case 'WL':
            case 'LW':
                $description = Craft::t('schedule', 'ComaOnTheLastWeekdayOfTheMonth');
                break;
            default:
                if (preg_match('#(\\d{1,2}W)|(W\\d{1,2})#', $expression, $match)) {
                    $dayNumber = (int)str_replace('W', '', $match[1]);
                    $dayString = $dayNumber == 1 ? Craft::t('schedule', 'FirstWeekday') : Craft::t('schedule', 'WeekdayNearestDay{0}', ['0' => $dayNumber]);
                    $description = Craft::t('schedule', 'ComaOnThe{0}OfTheMonth', ['0' => $dayString]);
                    break;
                } else {
                    // Handle "last day offset" (i.e. L-5:  "5 days before the last day of the month")
                    if (preg_match('#L-(\\d{1,2})#', $expression, $match)) {
                        $offSetDays = $match[1];
                        $description = Craft::t('schedule', 'CommaDaysBeforeTheLastDayOfTheMonth', $offSetDays);
                        break;
                    } else {
                        $description = $this->getSegmentDescription($expression,
                            Craft::t('schedule', 'ComaEveryDay'),
                            function ($s) {
                                return $s;
                            },
                            function ($s) {
                                return Craft::t('schedule', $s == '1' ? 'ComaEveryDay' : 'ComaEvery{0}Days');
                            },
                            function () {
                                return Craft::t('schedule', 'ComaBetweenDay{0}And{1}OfTheMonth');
                            },
                            function () {
                                return Craft::t('schedule', 'ComaOnDay{0}OfTheMonth');
                            },
                            function () {
                                return Craft::t('schedule', 'ComaX0ThroughX1');
                            });
                        break;
                    }
                }
        }

        return $description;
    }

    /**
     * @return string
     */
    public function getYearDescription(): string
    {
        $description = $this->getSegmentDescription($this->expression['year'], '',
            function ($s) {
                if (preg_match('#^\d+$#', $s)) {
                    return (new DateTime("$s year"))->format('Y');
                }

                return $s;
            },
            function ($s) {
                return Craft::t('schedule', 'ComaEvery{0}Years', ['0' => $s]);
            },
            function () {
                return Craft::t('schedule', 'Coma{0}Through{1}');
            },
            function () {
                return Craft::t('schedule', 'ComaOnlyIn{0}');
            },
            function () {
                return Craft::t('schedule', 'Coma{0}Through{1}');
            }
        );

        return $description;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @param string $expression
     * @param string $allDescription
     * @param callable $getSingleItemDescription
     * @param callable $getIntervalDescriptionFormat
     * @param callable $getBetweenDescriptionFormat
     * @param callable $getDescriptionFormat
     * @param callable $getRangeFormat
     * @return string|null
     */
    protected function getSegmentDescription(string $expression,
                                             string $allDescription,
                                             callable $getSingleItemDescription,
                                             callable $getIntervalDescriptionFormat,
                                             callable $getBetweenDescriptionFormat,
                                             callable $getDescriptionFormat,
                                             callable $getRangeFormat)
    {
        $description = null;
        if (empty($expression)) {
            return '';
        }

        if ($expression == '*') {
            $description = $allDescription;
        } elseif (!$this->contains($expression, ['/', '-', ','])) {
            $description = strtr($getDescriptionFormat($expression), ['{0}' => $getSingleItemDescription($expression)]);
        } elseif ($this->contains($expression, '/')) {
            $segments = explode('/', $expression);
            $description = strtr($getIntervalDescriptionFormat($segments[1]), ['{0}' => $getSingleItemDescription($segments[1])]);

            //interval contains 'between' piece (i.e. 2-59/3 )
            if (strpos($segments[0], '-')) {
                $betweenSegmentDescription = $this->generateBetweenSegmentDescription($segments[0], $getBetweenDescriptionFormat, $getSingleItemDescription);

                if (strpos($betweenSegmentDescription, ',') !== 0) {
                    $description .= ", ";
                }

                $description .= $betweenSegmentDescription;
            } elseif ($this->contains($segments[0], ['*', ',']) === false) {
                $rangeItemDescription = strtr($getDescriptionFormat($segments[0]), ['{0}' => $getSingleItemDescription($segments[0])]);
                //remove any leading comma
                $rangeItemDescription = str_replace(', ', '', $rangeItemDescription);

                $description .= Craft::t('schedule', 'CommaStarting{0}', ['0' => $rangeItemDescription]);
            }
        } elseif ($this->contains($expression, ',')) {
            $segments = explode(',', $expression);

            $descriptionContent = '';

            for ($i = 0; $i < count($segments); $i++) {
                if ($i > 0 && count($segments) > 2) {
                    $descriptionContent .= ',';

                    if ($i < count($segments) - 1) {
                        $descriptionContent .= ' ';
                    }
                }

                if ($i > 0 && count($segments) > 1 && ($i == count($segments) - 1 || count($segments) == 2)) {
                    $descriptionContent .= Craft::t('schedule', "SpaceAndSpace");
                }

                if (strpos($segments[$i], '-') !== false) {
                    $betweenSegmentDescription = $this->generateBetweenSegmentDescription($segments[$i], $getRangeFormat, $getSingleItemDescription);

                    //remove any leading comma
                    $betweenSegmentDescription = str_replace(', ', '', $betweenSegmentDescription);

                    $descriptionContent .= $betweenSegmentDescription;
                } else {
                    $descriptionContent .= $getSingleItemDescription($segments[$i]);
                }
            }

            $description = strtr($getDescriptionFormat($expression), ['{0}' => $descriptionContent]);
        } elseif ($this->contains($expression, '-')) {
            $description = $this->generateBetweenSegmentDescription($expression, $getBetweenDescriptionFormat, $getSingleItemDescription);
        }

        return $description;
    }

    /**
     * @param string $betweenExpression
     * @param callable $getBetweenDescriptionFormat
     * @param callable $getSingleItemDescription
     * @return string
     */
    protected function generateBetweenSegmentDescription(string $betweenExpression, callable $getBetweenDescriptionFormat, callable $getSingleItemDescription): string
    {
        $description = '';
        $betweenSegments = explode('-', $betweenExpression);
        $betweenSegment1Description = $getSingleItemDescription($betweenSegments[0]);
        $betweenSegment2Description = $getSingleItemDescription($betweenSegments[1]);
        $betweenSegment2Description = str_replace(':00', ':59', $betweenSegment2Description);
        $betweenDescriptionFormat = $getBetweenDescriptionFormat($betweenExpression);
        $description .= strtr($betweenDescriptionFormat, ['{0}' => $betweenSegment1Description, '{1}' => $betweenSegment2Description]);

        return $description;
    }

    /**
     * @param string $hour
     * @param string $minute
     * @return string
     */
    protected function formatTime(string $hour, string $minute): string
    {
        $period = '';

        if (!$this->use24HourTimeFormat) {
            $period = Craft::t('schedule', $hour >= 12 ? "PMPeriod" : "AMPeriod");

            if (!empty($period)) {
                $period = ' ' . $period;
            }

            if ($hour > 12) {
                $hour -= 12;
            } elseif ($hour == 0) {
                $hour = 12;
            }
        }

        return sprintf("%s:%s%s", str_pad($hour, 2, '0', STR_PAD_LEFT), str_pad($minute, 2, '0', STR_PAD_LEFT), $period);
    }

    /**
     * @param string $string
     * @param array|string|null $specialCharacters
     * @return bool
     */
    protected function contains(string $string, $specialCharacters = null)
    {
        $specialCharacters = $specialCharacters ?? $this->defaultSpecialCharacters;

        if (is_array($specialCharacters)) {
            foreach ($specialCharacters as $character) {
                if (($pos = strpos($string, $character)) !== false) {
                    return true;
                }
            }
        } else {
            return strpos($string, $specialCharacters) !== false;
        }

        return false;
    }
}