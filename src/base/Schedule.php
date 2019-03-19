<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\base;

use Craft;
use craft\base\SavableComponent;
use craft\helpers\DateTimeHelper;
use craft\validators\HandleValidator;
use DateTime;
use DateTimeZone;
use panlatent\schedule\Builder;
use panlatent\schedule\events\ScheduleEvent;
use panlatent\schedule\helpers\CronHelper;
use yii\db\Query;

/**
 * Class Schedule
 *
 * @package panlatent\schedule\base
 * @property-read string $cronExpression
 * @property-read string $cronDescription
 * @author Panlatent <panlatent@gmail.com>
 */
abstract class Schedule extends SavableComponent implements ScheduleInterface
{
    // Traits
    // =========================================================================

    use ScheduleTrait;

    // Constants
    // =========================================================================

    /**
     * @event ScheduleEvent
     */
    const EVENT_BEFORE_RUN = 'beforeRun';

    /**
     * @event ScheduleEvent
     */
    const EVENT_AFTER_RUN = 'afterRun';

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function isRunnable(): bool
    {
        return false;
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'handle', 'minute', 'hour', 'day', 'month', 'week', 'timer'], 'required'],
            [['groupId'], 'integer'],
            [['name', 'handle', 'description', 'minute', 'hour', 'day', 'month', 'week', 'user', 'timer'], 'string'],
            [['handle'], HandleValidator::class],
        ];
    }

    /**
     * Returns cron expression.
     *
     * @return string
     */
    public function getCronExpression(): string
    {
        return CronHelper::toCronExpression([$this->minute, $this->hour, $this->day, $this->month, $this->week]);
    }

    /**
     * Returns cron expression description.
     *
     * @return string
     */
    public function getCronDescription(): string
    {
        return CronHelper::toDescription($this->getCronExpression());
    }

    /**
     * @return DateTime|null
     */
    public function getDateLastStarted()
    {
        return $this->_getLastDate('dateLastStarted');
    }

    /**
     * @return DateTime|null
     */
    public function getDateLastFinished()
    {
        return $this->_getLastDate('dateLastFinished');
    }

    /**
     * @inheritdoc
     */
    public function build(Builder $builder)
    {
        if (static::isRunnable()) {
            /** @noinspection PhpParamsInspection */
            $builder->call([$this, 'run'])
                ->cron($this->getCronExpression());

            return;
        }
    }

    /**
     * @return bool
     */
    public function run(): bool
    {
        if (!$this->beforeRun()) {
            return false;
        }

        $this->execute();

        $this->afterRun();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function beforeRun(): bool
    {
        if ($this->hasEventHandlers(self::EVENT_BEFORE_RUN)) {
            $this->trigger(self::EVENT_BEFORE_RUN, new ScheduleEvent([
                'schedule' => $this,
            ]));
        }

        $this->_updateLastDate('dateLastStarted');

        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterRun()
    {
        $this->_updateLastDate('dateLastFinished');

        if ($this->hasEventHandlers(self::EVENT_AFTER_RUN)) {
            $this->trigger(self::EVENT_AFTER_RUN, new ScheduleEvent([
                'schedule' => $this,
            ]));
        }
    }

    // Protected Methods
    // =========================================================================

    protected function execute()
    {

    }

    // Private Methods
    // =========================================================================

    /**
     * @param string $field
     */
    private function _updateLastDate(string $field)
    {
        Craft::$app->getDb()->createCommand()
            ->update('{{%schedules}}', [
                $field => (new DateTime())->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
            ], [
                'id' => $this->id,
            ])
            ->execute();
    }

    /**
     * @param string $field
     * @return DateTime|false|null
     */
    private function _getLastDate(string $field)
    {
        $date = (new Query())
            ->select($field)
            ->from('{{%schedules}}')
            ->where(['id' => $this->id])
            ->scalar();

        if (!$date) {
            return null;
        }

        return DateTimeHelper::toDateTime($date);
    }
}