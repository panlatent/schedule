<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\services;

use Craft;
use craft\errors\MissingComponentException;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\ArrayHelper;
use craft\helpers\Component as ComponentHelper;
use craft\helpers\Json;
use panlatent\schedule\base\Timer;
use panlatent\schedule\base\TimerInterface;
use panlatent\schedule\db\Table;
use panlatent\schedule\errors\TimerException;
use panlatent\schedule\events\TimerEvent;
use panlatent\schedule\records\Timer as TimerRecord;
use panlatent\schedule\timers\Custom;
use panlatent\schedule\timers\DateTime;
use panlatent\schedule\timers\Every;
use panlatent\schedule\timers\MissingTimer;
use panlatent\schedule\timers\Relay;
use Throwable;
use yii\base\Component;
use yii\db\Query;

/**
 * Class Timers
 *
 * @package panlatent\schedule\services
 * @author Panlatent <panlatent@gmail.com>
 */
class Timers extends Component
{
    // Constants
    // =========================================================================

    /**
     * @event RegisterComponentEvent
     */
    const EVENT_REGISTER_TIMER_TYPES = 'registerTimerTypes';

    /**
     * @event TimerEvent The event that is triggered before a timer is saved.
     */
    const EVENT_BEFORE_SAVE_TIMER = 'beforeSaveTimer';

    /**
     * @event TimerEvent The event that is triggered after a timer is saved.
     */
    const EVENT_AFTER_SAVE_TIMER = 'afterSaveTimer';

    /**
     * @event TimerEvent The event that is triggered before a timer is deleted.
     */
    const EVENT_BEFORE_DELETE_TIMER = 'beforeDeleteTimer';

    /**
     * @event TimerEvent The event that is triggered after a timer is deleted.
     */
    const EVENT_AFTER_DELETE_TIMER = 'afterDeleteTimer';

    // Properties
    // =========================================================================

    /**
     * @var bool
     */
    public $_fetchedAllTimers = false;

    /**
     * @var TimerInterface[]|null
     */
    public $_timersById;

    // Public Methods
    // =========================================================================

    /**
     * @return string[]
     */
    public function getAllTimerTypes(): array
    {
        $types = [
            Custom::class,
            DateTime::class,
            Every::class,
            Relay::class,
        ];

        $event = new RegisterComponentTypesEvent([
            'types' => $types,
        ]);

        $this->trigger(self::EVENT_REGISTER_TIMER_TYPES, $event);

        return $event->types;
    }

    /**
     * @return TimerInterface[]
     */
    public function getAllTimers(): array
    {
        if ($this->_fetchedAllTimers) {
            return array_values($this->_timersById);
        }

        $this->_timersById = [];

        $results = $this->_createQuery()->all();
        foreach ($results as $result) {
            /** @var Timer $timer */
            $timer = $this->createTimer($result);
            $this->_timersById[$timer->id] = $timer;
        }

        $this->_fetchedAllTimers = true;

        return array_values($this->_timersById);
    }

    /**
     * @return TimerInterface[]
     */
    public function getActiveTimers(): array
    {
        return array_filter($this->getAllTimers(), function(TimerInterface $timer) {
            return $timer->isValid();
        });
    }

    /**
     * @param int $scheduleId
     * @return TimerInterface[]
     */
    public function getTimersByScheduleId(int $scheduleId)
    {
        if ($this->_fetchedAllTimers) {
            return ArrayHelper::filterByValue($this->getAllTimers(), 'scheduleId', $scheduleId);
        }

        $ret = [];

        $results = $this->_createQuery()
            ->where(['scheduleId' => $scheduleId])
            ->all();

        foreach ($results as $result) {
            /** @var Timer $timer */
            $timer = $this->createTimer($result);
            $ret[] = $this->_timersById[$timer->id] = $timer;
        }

        return $ret;
    }

    /**
     * @param int $id
     * @return TimerInterface|null
     */
    public function getTimerById(int $id)
    {
        if ($this->_timersById && array_key_exists($id, $this->_timersById)) {
            return $this->_timersById[$id];
        }

        if ($this->_fetchedAllTimers) {
            return null;
        }

        $result = $this->_createQuery()
            ->where(['id' => $id])
            ->one();

        return $this->_timersById[$id] = $result ? $this->createTimer($result) : null;
    }

    /**
     * @param mixed $config
     * @return TimerInterface
     */
    public function createTimer($config): TimerInterface
    {
        try {
            $timer = ComponentHelper::createComponent($config, TimerInterface::class);
        } catch (MissingComponentException $exception) {
            unset($config['type']);
            $timer = new MissingTimer($config);
        }

        return $timer;
    }

    /**
     * @param TimerInterface $timer
     * @param bool $runValidation
     * @return bool
     */
    public function saveTimer(TimerInterface $timer, bool $runValidation = true): bool
    {
        /** @var Timer $timer */
        $isNewTimer = !$timer->id;

        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_TIMER)) {
            $this->trigger(self::EVENT_BEFORE_SAVE_TIMER, new TimerEvent([
                'timer' => $timer,
                'isNew' => $isNewTimer,
            ]));
        }

        if (!$timer->beforeSave($isNewTimer)) {
            return false;
        }

        if ($runValidation && !$timer->validate()) {
            Craft::info("Timer not saved due to validation error.", __METHOD__);
            return false;
        }

        $transaction = Craft::$app->getDb()->beginTransaction();
        try {
            if (!$isNewTimer) {
                $record = TimerRecord::findOne(['id' => $timer->id]);
                if (!$record) {
                    throw new TimerException("No timer exists with the ID: “{$timer->id}“.");
                }
            } else {
                $record = new TimerRecord();
            }

            if ($isNewTimer) {
                $record->scheduleId = $timer->scheduleId;
            }

            $record->type = get_class($timer);
            $record->minute = $this->_normalizeCronExpress($timer->minute);
            $record->hour = $this->_normalizeCronExpress($timer->hour);
            $record->day = $this->_normalizeCronExpress($timer->day);
            $record->month = $this->_normalizeCronExpress($timer->month);
            $record->week = $this->_normalizeCronExpress($timer->week);
            $record->enabled = $timer->enabled;
            $record->settings = Json::encode($timer->getSettings());

            if ($isNewTimer) {
                $lastSortOrder = (new Query())
                    ->select('sortOrder')
                    ->from(Table::SCHEDULETIMERS)
                    ->where([
                        'scheduleId' => $timer->scheduleId,
                    ])
                    ->orderBy('sortOrder')
                    ->scalar();

                $record->sortOrder = (int)$lastSortOrder + 1;
            }

            $record->save(false);

            if ($isNewTimer) {
                $timer->id = $record->id;
            }

            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        $this->_timersById[$timer->id] = $timer;

        $timer->afterSave($isNewTimer);

        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_TIMER)) {
            $this->trigger(self::EVENT_AFTER_SAVE_TIMER, new TimerEvent([
                'timer' => $timer,
                'isNew' => $isNewTimer,
            ]));
        }

        return true;
    }

    /**
     * @param TimerInterface $timer
     * @return bool
     */
    public function deleteTimer(TimerInterface $timer): bool
    {
        /** @var Timer $timer */
        if ($this->hasEventHandlers(self::EVENT_BEFORE_DELETE_TIMER)) {
            $this->trigger(self::EVENT_BEFORE_DELETE_TIMER, new TimerEvent([
                'timer' => $timer,
            ]));
        }

        $db = Craft::$app->getDb();

        $transaction = $db->beginTransaction();
        try {
            $db->createCommand()
                ->delete(Table::SCHEDULETIMERS, [
                    'id' => $timer->id,
                ])
                ->execute();

            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE_TIMER)) {
            $this->trigger(self::EVENT_AFTER_DELETE_TIMER, new TimerEvent([
                'timer' => $timer,
            ]));
        }

        return true;
    }

    /**
     * @param array $timerIds
     * @return bool
     */
    public function reorderTimers(array $timerIds): bool
    {
        $db = Craft::$app->getDb();

        $transaction = $db->beginTransaction();
        try {
            foreach ($timerIds as $order => $id) {
                $db->createCommand()
                    ->update(Table::SCHEDULETIMERS, [
                        'sortOrder' => $order + 1
                    ], [
                        'id' => $id,
                    ])
                    ->execute();
            }

            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        return true;
    }

    public function deleteTimersByScheduleId(int $scheduleId)
    {

    }

    // Private Methods
    // =========================================================================

    /**
     * @return Query
     */
    private function _createQuery(): Query
    {
        return (new Query())
            ->select([
                'id',
                'scheduleId',
                'type',
                'minute',
                'hour',
                'day',
                'month',
                'week',
                'settings',
                'enabled',
                'sortOrder'
            ])
            ->from(Table::SCHEDULETIMERS)
            ->orderBy(['sortOrder' => SORT_ASC]);
    }

    /**
     * @param mixed $express
     * @return string
     */
    private function _normalizeCronExpress($express): string
    {
        return ($express !== '' && $express !== null) ? $express : '*';
    }
}