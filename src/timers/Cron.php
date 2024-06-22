<?php

namespace panlatent\schedule\timers;

use Craft;
use craft\helpers\DateTimeHelper;
use Cron\CronExpression;
use DateTimeZone;
use panlatent\schedule\base\Timer;
use panlatent\schedule\helpers\CronHelper;

/**
 * @property-read ?\DateTime $datetime
 */
class Cron extends Timer
{
    public const MODE_EVERY = 'every';
    public const MODE_DATETIME = 'datetime';
    public const MODE_EXPRESSION = 'expression';

    public const EVERY_MINUTE = 'minute';
    public const EVERY_HOURLY = 'hourly';
    public const EVERY_DAILY = 'daily';
    public const EVERY_WEEKLY = 'weekly';
    public const EVERY_MONTHLY = 'monthly';
    public const EVERY_YEARLY = 'yearly';

    public static function displayName(): string
    {
        return Craft::t('schedule', 'Cron');
    }

    public string $mode = self::MODE_EVERY;

    /**
     * @var string
     */
    public string $minute = '*';

    /**
     * @var string
     */
    public string $hour = '*';

    /**
     * @var string
     */
    public string $day = '*';

    /**
     * @var string
     */
    public string $month = '*';

    /**
     * @var string
     */
    public string $week = '*';

    public string $year = '*';

    /**
     * @var string|null
     */
    public ?string $timezone = null;

    /**
     * @return array
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [['minute', 'hour', 'day', 'month', 'week'], 'string'];
        $rules[] = [['minute', 'hour', 'day', 'month', 'week'], function($property) {
            if ($this->$property === '') {
                $this->$property = '*';
            }
        }];

        return $rules;
    }

    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('schedule/_components/timers/Cron', [
            'timer' => $this,
            'modeOptions' => [
                ['label' => Craft::t('schedule', 'Every'), 'value' => self::MODE_EVERY],
                ['label' => Craft::t('schedule', 'DateTime'), 'value' => self::MODE_DATETIME],
                ['label' => Craft::t('schedule', 'Expression'), 'value' => self::MODE_EXPRESSION],
            ]
        ]);
    }

    public function isDue(): bool
    {
        $now = new \DateTime('now', $this->timezone);
        if (($this->mode === self::MODE_DATETIME) && $this->getDatetime()?->format('Y') !== $now->format('Y')) {
            return false;
        }

        return (new CronExpression($this->getCronExpression()))->isDue($now);
    }

    public function getCronExpression(): string
    {
        return sprintf('%s %s %s %s %s', $this->minute, $this->hour, $this->day, $this->month, $this->week);
    }

    public function setCronExpression(string $cron): void
    {
        [$this->minute, $this->hour, $this->day, $this->month, $this->week, ] = explode(' ', $cron);
    }

    public function getCronDescription(): string
    {
        return CronHelper::toDescription($this->getCronExpression());
    }

    public function getDatetime(): ?\DateTime
    {
        if ($this->year === '*') {
            return null;
        }
        $timezone = new DateTimeZone($this->timezone ?: Craft::$app->getTimeZone());
        $datetime = sprintf("%d-%d-%d %d:%d", $this->year, $this->month, $this->day, $this->hour, $this->minute);
        return new \DateTime($datetime, $timezone);
    }

    public function setDateTime(mixed $datetime): void
    {
        $datetime = DateTimeHelper::toDateTime($datetime);
        if (!$datetime) {
            return;
        }
        $this->timezone = $datetime->getTimezone()->getName();
        $this->year = $datetime->format('Y');
        $this->month = $datetime->format('m');
        $this->day = $datetime->format('d');
        $this->hour = $datetime->format('H');
        $this->minute = $datetime->format('m');
    }

    public function getEvery(): string
    {
        if ($this->mode !== self::MODE_EVERY){
            return self::EVERY_MINUTE;
        }
        return match ($this->getCronExpression()) {
            '* * * * *' => self::EVERY_MINUTE,
            '0 * * * *' => self::EVERY_HOURLY,
            '0 0 * * *' => self::EVERY_DAILY,
            '0 0 1 * *' => self::EVERY_MONTHLY,
            '0 0 * * 0' => self::EVERY_WEEKLY,
            '0 0 1 1 *' => self::EVERY_YEARLY,
        };
    }

    public function setEvery(string $unit): void
    {
        $this->setCronExpression(match ($unit) {
            self::EVERY_MINUTE => '* * * * *',
            self::EVERY_HOURLY => '0 * * * *',
            self::EVERY_DAILY => '0 0 * * *',
            self::EVERY_MONTHLY => '0 0 1 * *',
            self::EVERY_WEEKLY => '0 0 * * 0',
            self::EVERY_YEARLY => '0 0 1 1 *',
        });
    }

    public function getEveryOptions(): array
    {
        return [
            Craft::t('schedule', 'Minute') => self::EVERY_MINUTE,
            Craft::t('schedule', 'Hourly') => self::EVERY_HOURLY,
            Craft::t('schedule', 'Daily') => self::EVERY_DAILY,
            Craft::t('schedule', 'Monthly') => self::EVERY_MONTHLY,
            Craft::t('schedule', 'Yearly') => self::EVERY_YEARLY,
            Craft::t('schedule', 'Weekly') => self::EVERY_WEEKLY,
        ];
    }

    protected function defineRules(): array
    {
        return [
            [['mode'], 'required'],
            [['minute', 'hour', 'day', 'month', 'week', 'year'], 'string'],
        ];
    }
}