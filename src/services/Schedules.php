<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\services;

use Craft;
use craft\errors\DeprecationException;
use craft\errors\MissingComponentException;
use craft\events\ConfigEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\ArrayHelper;
use craft\helpers\Component as ComponentHelper;
use craft\helpers\Db;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use craft\web\Request;
use panlatent\schedule\base\Schedule;
use panlatent\schedule\base\ScheduleInterface;
use panlatent\schedule\db\Table;
use panlatent\schedule\errors\ScheduleException;
use panlatent\schedule\errors\ScheduleGroupException;
use panlatent\schedule\events\ScheduleEvent;
use panlatent\schedule\events\ScheduleGroupEvent;
use panlatent\schedule\models\ScheduleCriteria;
use panlatent\schedule\models\ScheduleGroup;
use panlatent\schedule\Plugin;
use panlatent\schedule\records\Schedule as ScheduleRecord;
use panlatent\schedule\records\ScheduleGroup as ScheduleGroupRecord;
use panlatent\schedule\schedules\Console;
use panlatent\schedule\schedules\Event;
use panlatent\schedule\schedules\HttpRequest;
use panlatent\schedule\schedules\MissingSchedule;
use panlatent\schedule\schedules\Queue;
use Throwable;
use yii\base\Component;
use yii\db\Expression;
use yii\db\Query;

/**
 * Class Schedules
 *
 * @package panlatent\schedule\services
 * @author Panlatent <panlatent@gmail.com>
 */
class Schedules extends Component
{
    // Constants
    // =========================================================================

    /**
     * @event RegisterComponentTypesEvent
     * @deprecated Since 1.0.0
     */
    public const EVENT_REGISTER_ALL_SCHEDULE_TYPES = 'registerAllScheduleTypes';

    /**
     * @event ScheduleGroupEvent The event that is triggered before a tag group is saved.
     */
    public const EVENT_BEFORE_SAVE_GROUP = 'beforeSaveGroup';

    /**
     * @event ScheduleGroupEvent The event that is triggered after a tag group is saved.
     */
    public const EVENT_AFTER_SAVE_GROUP = 'afterSaveGroup';

    /**
     * @event ScheduleGroupEvent The event that is triggered before a tag group is deleted.
     */
    public const EVENT_BEFORE_DELETE_GROUP = 'beforeDeleteGroup';

    /**
     * @event ScheduleGroupEvent The event that is triggered after a tag group is deleted.
     */
    public const EVENT_AFTER_DELETE_GROUP = 'afterDeleteGroup';

    /**
     * @event ScheduleGroupEvent The event that is triggered before a tag group is applied.
     */
    public const EVENT_BEFORE_APPLY_DELETE_GROUP = 'beforeApplyDeleteGroup';

    /**
     * @event ScheduleEvent
     */
    public const EVENT_BEFORE_SAVE_SCHEDULE = 'beforeSaveSchedule';

    /**
     * @event ScheduleEvent
     */
    public const EVENT_AFTER_SAVE_SCHEDULE = 'afterSaveSchedule';

    /**
     * @event ScheduleEvent
     */
    public const EVENT_BEFORE_DELETE_SCHEDULE = 'beforeDeleteSchedule';

    /**
     * @event ScheduleEvent
     */
    public const EVENT_AFTER_DELETE_SCHEDULE = 'afterDeleteSchedule';

    /**
     * @event ScheduleEvent
     */
    public const EVENT_BEFORE_APPLY_DELETE_SCHEDULE = 'beforeApplyDeleteSchedule';

    // Properties
    // =========================================================================

    /**
     * @var bool Force fetch groups or schedules. (Not cache)
     * @deprecated Since 1.0.0
     */
    public bool $force = false;

    /**
     * @var ScheduleGroup[]|null
     */
    private ?array $_groups = null;

    /**
     * @var ScheduleInterface[]|null
     */
    private ?array $_schedules = null;

    /**
     * Returns all category groups.
     *
     * @return ScheduleGroup[]
     */
    public function getAllGroups(): array
    {
        if ($this->_groups === null) {
            $this->_groups = [];
            $results = $this->_createGroupQuery()->all();
            foreach ($results as $result) {
                $group = $this->createGroup($result);
                $this->_groups[] = $group;
            }
        }

        return$this->_groups;
    }

    /**
     * Returns a group by its ID.
     *
     * @param int $groupId
     * @return ScheduleGroup|null
     */
    public function getGroupById(int $groupId): ?ScheduleGroup
    {
        return ArrayHelper::firstWhere($this->getAllGroups(), 'id', $groupId);
    }

    /**
     * Returns a group by its name.
     *
     * @param string $name
     * @return ScheduleGroup|null
     */
    public function getGroupByHandle(string $name): ?ScheduleGroup
    {
        return ArrayHelper::firstWhere($this->getAllGroups(), 'handle', $name);
    }

    /**
     * Returns a group by its UID.
     *
     * @param string $uid
     * @return ScheduleGroup|null
     */
    public function getGroupByUid(string $uid): ?ScheduleGroup
    {
        return ArrayHelper::firstWhere($this->getAllGroups(), 'uid', $uid);
    }

    /**
     * Create a group.
     *
     * @param mixed $config
     * @return ScheduleGroup
     */
    public function createGroup(mixed $config): ScheduleGroup
    {
        return new ScheduleGroup($config);
    }

    /**
     * Save a group.
     *
     * @param ScheduleGroup $group
     * @param bool $runValidation
     * @return bool
     */
    public function saveGroup(ScheduleGroup $group, bool $runValidation = true): bool
    {
        $isNewGroup = !$group->id;

        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_GROUP)) {
            $this->trigger(self::EVENT_BEFORE_SAVE_GROUP, new ScheduleGroupEvent([
                'group' => $group,
                'isNew' => $isNewGroup,
            ]));
        }

        if ($runValidation && !$group->validate()) {
            Craft::info('Schedule group not saved due to validation error.', __METHOD__);
            return false;
        }

        if (!$isNewGroup) {
            $groupRecord = ScheduleGroupRecord::findOne(['id' => $group->id]);
            if (!$groupRecord) {
                throw new ScheduleGroupException("No group exists with the ID “{$group->id}“");
            }
        } else {
            $groupRecord = new ScheduleGroupRecord();
        }

        $groupRecord->name = $group->name;
        $groupRecord->save(false);

        if ($isNewGroup) {
            $group->id = $groupRecord->id;
            $this->_groups[] = $group;
        }

        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_GROUP)) {
            $this->trigger(self::EVENT_AFTER_SAVE_GROUP, new ScheduleGroupEvent([
                'group' => $group,
                'isNew' => $isNewGroup,
            ]));
        }

        return true;
    }

    /**
     * Delete a group.
     *
     * @param ScheduleGroup $group
     * @return bool
     */
    public function deleteGroup(ScheduleGroup $group): bool
    {
        if ($this->hasEventHandlers(self::EVENT_BEFORE_DELETE_GROUP)) {
            $this->trigger(self::EVENT_BEFORE_DELETE_GROUP, new ScheduleGroupEvent([
                'group' => $group,
            ]));
        }

        Craft::$app->getDb()->createCommand()->delete(Table::SCHEDULEGROUPS, [
            'id' => $group->id,
        ])->execute();

        if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE_GROUP)) {
            $this->trigger(self::EVENT_AFTER_DELETE_GROUP, new ScheduleGroupEvent([
                'group' => $group,
            ]));
        }

        return true;
    }

    // Schedules
    // =========================================================================

    /**
     * @return string[]
     * @deprecated Since 1.0.0
     */
    public function getAllScheduleTypes(): array
    {
        throw new DeprecationException();
    }

    /**
     * @return ScheduleInterface[]
     */
    public function getAllSchedules(): array
    {
        if ($this->_schedules === null) {
            $this->_schedules = [];
            $results = $this->_createScheduleQuery()->all();
            foreach ($results as $result) {
                $schedule = $this->createSchedule($result);
                $this->_schedules[] = $schedule;
            }
        }

        return $this->_schedules;
    }

    /**
     * @return ScheduleInterface[]
     */
    public function getActiveSchedules(): array
    {
        return array_filter($this->getAllSchedules(), function(ScheduleInterface $schedule) {
            return $schedule->isValid();
        });
    }

    /**
     * @return ScheduleInterface[]
     */
    public function getStaticSchedules(): array
    {
        return ArrayHelper::where($this->getAllSchedules(), 'static');
    }

    /**
     * @return int
     */
    public function getTotalStaticSchedules(): int
    {
        return $this->_createScheduleQuery()->where(['schedules.static' => true])->count('[[schedules.id]]');
    }

    /**
     * @param int|null $groupId
     * @return ScheduleInterface[]
     */
    public function getSchedulesByGroupId(int $groupId = null): array
    {
        return ArrayHelper::where($this->getAllSchedules(), 'groupId', $groupId);
    }

    /**
     * @param int $scheduleId
     * @return ScheduleInterface|null
     */
    public function getScheduleById(int $scheduleId): ?ScheduleInterface
    {
        return ArrayHelper::firstWhere($this->getAllSchedules(), 'id', $scheduleId);
    }

    /**
     * @param string $handle
     * @return ScheduleInterface|null
     */
    public function getScheduleByHandle(string $handle): ?ScheduleInterface
    {
        return ArrayHelper::firstWhere($this->getAllSchedules(), 'handle', $handle);
    }

    /**
     * @param string $uid
     * @return ScheduleInterface|null
     */
    public function getScheduleByUid(string $uid): ?ScheduleInterface
    {
        return ArrayHelper::firstWhere($this->getAllSchedules(), 'uid', $uid);
    }

    /**
     * @param array|ScheduleCriteria $criteria
     * @return ScheduleInterface[]
     */
    public function findSchedules(array|ScheduleCriteria $criteria): array
    {
        if (!$criteria instanceof ScheduleCriteria) {
            $criteria = new ScheduleCriteria($criteria);
        }

        $query = $this->_createScheduleQuery()
            ->orderBy($criteria->sortOrder)
            ->offset($criteria->offset)
            ->limit($criteria->limit);

        $this->_applyConditions($query, $criteria);

        $schedules = [];
        $results = $query->all();
        foreach ($results as $result) {
            $schedules[] = $this->createSchedule($result);
        }

        return $schedules;
    }

    /**
     * @param array|ScheduleCriteria $criteria
     * @return ScheduleInterface|null
     */
    public function findSchedule(array|ScheduleCriteria $criteria): ?ScheduleInterface
    {
        if (!$criteria instanceof ScheduleCriteria) {
            $criteria = new ScheduleCriteria($criteria);
        }

        $criteria->limit = 1;

        $results = $this->findSchedules($criteria);
        if (!$results) {
            return null;
        }

        return array_pop($results);
    }

    /**
     * @param array|ScheduleCriteria $criteria
     * @return int
     */
    public function getTotalSchedules(array|ScheduleCriteria $criteria = []): int
    {
        if (!$criteria instanceof ScheduleCriteria) {
            $criteria = new ScheduleCriteria($criteria);
        }
        $query = $this->_createScheduleQuery();
        $this->_applyConditions($query, $criteria);

        return $query->count('[[schedules.id]]');
    }

    /**
     * @param Request|null $request
     * @return ScheduleInterface
     */
    public function createScheduleFromRequest(Request $request = null): ScheduleInterface
    {
        if ($request === null) {
            $request = Craft::$app->getRequest();
        }

        $type = $request->getBodyParam('type');

        return $this->createSchedule([
            'id' => $request->getBodyParam('scheduleId'),
            'groupId' => $request->getBodyParam('groupId'),
            'name' => $request->getBodyParam('name'),
            'handle' => $request->getBodyParam('handle'),
            'description' => $request->getBodyParam('description'),
            'type' => $type,
            'settings' => $request->getBodyParam('types.' . $type, []),
            'static' => (bool)$request->getBodyParam('static'),
            'enabled' => (bool)$request->getBodyParam('enabled'),
            'enabledLog' => $request->getBodyParam('enabledLog'),
        ]);
    }

    /**
     * @param mixed $config
     * @return ScheduleInterface
     */
    public function createSchedule(mixed $config): ScheduleInterface
    {
        try {
            $schedule = ComponentHelper::createComponent($config, ScheduleInterface::class);
        } catch (MissingComponentException) {
            unset($config['type']);
            $schedule = new MissingSchedule($config);
        }

        return $schedule;
    }

    /**
     * @param ScheduleInterface $schedule
     * @param bool $runValidation
     * @return bool
     */
    public function saveSchedule(ScheduleInterface $schedule, bool $runValidation = true): bool
    {
        /** @var Schedule $schedule */
        $isNewSchedule = $schedule->getIsNew();

        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_SCHEDULE)) {
            $this->trigger(self::EVENT_BEFORE_SAVE_SCHEDULE, new ScheduleEvent([
                'schedule' => $schedule,
                'isNew' => $isNewSchedule,
            ]));
        }

        if (!$schedule->beforeSave($isNewSchedule)) {
            return false;
        }

        if ($isNewSchedule) {
            $schedule->uid = StringHelper::UUID();
        } elseif ($schedule->uid === null) {
            $schedule->uid = Db::uidById(Table::SCHEDULES, $schedule->id);
        }

        if ($runValidation && !$schedule->validate()) {
            Craft::info("Schedule not saved due to validation error.", __METHOD__);
            return false;
        }

        if ($schedule->static) {
            $path = "schedule.schedules.$schedule->uid";
            $config = $this->getScheduleConfig($schedule);
            if (!$isNewSchedule) {
                $config['timers'] = [];
                foreach ($schedule->getTimers() as $timer) {
                    $config['timers'][$timer->uid] = Plugin::$plugin->getTimers()->getTimerConfig($timer);
                }
            }

            Craft::$app->getProjectConfig()->set($path, $config);
            if ($isNewSchedule) {
                $schedule->id = Db::idByUid(Table::SCHEDULES, $schedule->uid);
            }
        } else {
            $transaction = Craft::$app->getDb()->beginTransaction();
            try {
                $deleteConfig = false;
                if (!$isNewSchedule) {
                    $record = ScheduleRecord::findOne(['id' => $schedule->id]);
                    if (!$record) {
                        throw new ScheduleException("No schedule exists with the ID: “{$schedule->id}“.");
                    }
                    if ($record->static) {
                        $deleteConfig = true;
                    }
                } else {
                    $record = new ScheduleRecord();
                }

                $record->groupId = $schedule->groupId;
                $record->name = $schedule->name;
                $record->handle = $schedule->handle;
                $record->description = $schedule->description;
                $record->type = get_class($schedule);
                $record->user = $schedule->user;
                $record->settings = Json::encode($schedule->getSettings());
                $record->static = false;
                $record->enabled = (bool)$schedule->enabled;
                $record->enabledLog = (bool)$schedule->enabledLog;
                $record->save(false);

                $transaction->commit();

                if ($deleteConfig) {
                    $path = "schedule.schedules.$record->uid";
                    Craft::$app->getProjectConfig()->remove($path);
                }
            } catch (Throwable $exception) {
                $transaction->rollBack();

                throw $exception;
            }


            if ($isNewSchedule) {
                $schedule->id = $record->id;
            }
        }

        if ($isNewSchedule) {
            $this->_schedules[] = $schedule;
        }

        $schedule->afterSave($isNewSchedule);

        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_SCHEDULE)) {
            $this->trigger(self::EVENT_AFTER_SAVE_SCHEDULE, new ScheduleEvent([
                'schedule' => $schedule,
                'isNew' => $isNewSchedule,
            ]));
        }

        return true;
    }

    /**
     * Reorders schedules.
     *
     * @param array $scheduleIds
     * @return bool
     */
    public function reorderSchedules(array $scheduleIds): bool
    {
        $db = Craft::$app->getDb();

        $transaction = $db->beginTransaction();
        try {
            foreach ($scheduleIds as $scheduleOrder => $scheduleId) {
                $db->createCommand()->update(Table::SCHEDULES, [
                    'sortOrder' => $scheduleOrder,
                ], [
                    'id' => $scheduleId,
                ])->execute();
            }
            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * Delete a schedule.
     *
     * @param ScheduleInterface $schedule
     * @return bool
     */
    public function deleteSchedule(ScheduleInterface $schedule): bool
    {
        /** @var Schedule $schedule */
        if ($this->hasEventHandlers(self::EVENT_BEFORE_DELETE_SCHEDULE)) {
            $this->trigger(self::EVENT_BEFORE_DELETE_SCHEDULE, new ScheduleEvent([
                'schedule' => $schedule,
            ]));
        }

        $schedule->beforeDelete();

        if ($schedule->static) {
            $path = "schedule.schedules.$schedule->uid";
            Craft::$app->getProjectConfig()->remove($path);
        } else {
            $db = Craft::$app->getDb();
            $transaction = $db->beginTransaction();
            try {
                $db->createCommand()->delete(Table::SCHEDULES, [
                    'id' => $schedule->id,
                ])->execute();

                $transaction->commit();

                $schedule->afterDelete();
            } catch (Throwable $exception) {
                $transaction->rollBack();

                throw $exception;
            }

            if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE_SCHEDULE)) {
                $this->trigger(self::EVENT_AFTER_DELETE_SCHEDULE, new ScheduleEvent([
                    'schedule' => $schedule,
                ]));
            }
        }

        return true;
    }

    public function handleChangeSchedule(ConfigEvent $event): void
    {
        $uid = $event->tokenMatches[0];

        $id = Db::idByUid(Table::SCHEDULES, $uid);
        $isNew = empty($id);

        $config = [
            'groupId' => null,
            'name' => $event->newValue['name'],
            'handle' => $event->newValue['handle'],
            'description' => $event->newValue['description'],
            'type' => $event->newValue['type'],
            'user' => $event->newValue['user'],
            'settings' => Json::encode($event->newValue['settings']),
            'static' => true,
            'enabled' => (bool)$event->newValue['enabled'],
            'enabledLog' => (bool)$event->newValue['enabledLog'],
        ];

        if ($isNew) {
            $config['uid'] = $uid;
            Db::insert(Table::SCHEDULES, $config);
        } else {
            Db::update(Table::SCHEDULES, $config, ['id' => $id]);
        }

        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_SCHEDULE)) {
            $this->trigger(self::EVENT_AFTER_SAVE_SCHEDULE, new ScheduleEvent([
                'schedule' => $this->getScheduleByUid($uid),
                'isNew' => $isNew,
            ]));
        }
    }

    public function handleDeleteSchedule(ConfigEvent $event): void
    {
        $uid = $event->tokenMatches[0];

        $schedule = $this->getScheduleByUid($uid);
        // non-static schedules cannot be deleted
        if (!$schedule || !$schedule->static) {
            return;
        }

        if ($this->hasEventHandlers(self::EVENT_BEFORE_APPLY_DELETE_SCHEDULE)) {
            $this->trigger(self::EVENT_BEFORE_APPLY_DELETE_SCHEDULE, new ScheduleEvent([
                'schedule' => $schedule,
            ]));
        }

        Db::delete(Table::SCHEDULES, ['id' => $schedule->id]);

        if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE_SCHEDULE)) {
            $this->trigger(self::EVENT_AFTER_DELETE_SCHEDULE, new ScheduleEvent([
                'schedule' => $schedule,
            ]));
        }
    }

    /**
     * @param ScheduleInterface $schedule
     * @return array
     */
    public function getScheduleConfig(ScheduleInterface $schedule): array
    {
        return [
            'name' => $schedule->name,
            'handle' => $schedule->handle,
            'description' => $schedule->description,
            'type' => get_class($schedule),
            'user' => $schedule->user,
            'settings' => $schedule->getSettings(),
            'enabled' => (bool)$schedule->enabled,
            'enabledLog' => (bool)$schedule->enabledLog,
        ];
    }

    // Private Methods
    // =========================================================================

    /**
     * @return Query
     */
    private function _createGroupQuery(): Query
    {
        return (new Query())
            ->select(['id', 'name', 'uid'])
            ->from(Table::SCHEDULEGROUPS);
    }

    /**
     * @return Query
     */
    private function _createScheduleQuery(): Query
    {
        return (new Query())
            ->select([
                'schedules.id',
                'schedules.groupId',
                'schedules.name',
                'schedules.handle',
                'schedules.description',
                //'schedules.type',
                //'schedules.user',
                //'schedules.settings',
                'schedules.static',
                'schedules.enabled',
                'schedules.enabledLog',
                'schedules.lastStartedTime',
                'schedules.lastFinishedTime',
                'schedules.lastStatus',
                'schedules.dateCreated',
                'schedules.dateUpdated',
                'schedules.uid',
            ])
            ->from(['schedules' => Table::SCHEDULES])
            ->orderBy('schedules.sortOrder');
    }

    /**
     * @param Query $query
     * @param ScheduleCriteria $criteria
     */
    private function _applyConditions(Query $query, ScheduleCriteria $criteria): void
    {
        if ($criteria->enabledLog !== null) {
            $query->andWhere(Db::parseParam('schedules.enabledLog', $criteria->enabledLog));
        }

        if ($criteria->hasLogs !== null) {
            $query->andWhere([
                '=',
                'schedules.id',
                (new Query())
                    ->select('logs.scheduleId')
                    ->from(Table::SCHEDULELOGS . ' logs')
                    ->where([
                        'logs.scheduleId' => new Expression('schedules.id'),
                    ])
                    ->limit(1),
            ]);
        }

        if ($criteria->search) {
            $query->andWhere(['like', 'schedules.name',  $criteria->search]);
        }
    }
}
