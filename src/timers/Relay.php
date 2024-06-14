<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\timers;

use Craft;
use Cron\CronExpression;
use DateInterval;
use DateTime;
use panlatent\schedule\base\Timer;

/**
 * Class Relay
 *
 * @package panlatent\schedule\timers
 * @author Panlatent <panlatent@gmail.com>
 */
class Relay extends Timer
{
    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('schedule', 'Relay');
    }

    // Properties
    // =========================================================================

    /**
     * @var int Wait time (minute)
     */
    public int $wait = 1;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [['wait'], 'integer', 'min' => 1];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        $attributeLabels = parent::attributeLabels();
        $attributeLabels['wait'] = Craft::t('schedule', 'Wait Time');

        return $attributeLabels;
    }

    public function isDue(): bool
    {
        $schedule = $this->getSchedule();
        $lastFinishedTime = $schedule->getInfo()->getLastFinishedTime();
        if (!$lastFinishedTime) {
            return true;
        }
        $date = $lastFinishedTime->add(new DateInterval("PT{$this->wait}M"));
        return $date->format('YmdHi') <= date('YmdHi');

//        $now = new \DateTime('now');
//        return (new CronExpression($this->getCronExpression()))->isDue($now);
    }

//    public function getCronExpression(): string
//    {
//        $schedule = $this->getSchedule();
//
//        $lastFinishedTime = $schedule->getInfo()->getLastFinishedTime();
//        if (!$lastFinishedTime) {
//            return '* * * * *';
//        }
//
//        $date = $lastFinishedTime->add(new DateInterval("PT{$this->wait}M"));
//        if ($date->format('YmdHi') <= date('YmdHi')) {
//            $date = new DateTime('now');
//        }
//
//        return $date->format('i H d m *');
//    }

    /**
     * @inheritdoc
     */
    public function getCronDescription(): string
    {
        return Craft::t('schedule', 'Wait {wait} minutes after last executed', [
            'wait' => $this->wait,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('schedule/_components/timers/Relay', [
            'timer' => $this,
        ]);
    }
}