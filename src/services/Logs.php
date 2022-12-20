<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\services;

use Carbon\Carbon;
use Craft;
use craft\db\Query;
use craft\helpers\Db;
use panlatent\schedule\base\Schedule;
use panlatent\schedule\db\Table;
use panlatent\schedule\models\LogCriteria;
use panlatent\schedule\models\ScheduleLog;
use yii\base\Component;

/**
 * Class Logs
 *
 * @package panlatent\schedule\services
 * @author Panlatent <panlatent@gmail.com>
 */
class Logs extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * @param LogCriteria|array|null $criteria
     * @return ScheduleLog[]
     */
    public function findLogs($criteria): array
    {
        if (!$criteria instanceof LogCriteria) {
            $criteria = new LogCriteria($criteria);
        }

        $query = $this->_createQuery()
            ->orderBy($criteria->sortOrder)
            ->offset($criteria->offset)
            ->limit($criteria->limit);

        $this->_applyConditions($query, $criteria);

        $logs = [];
        $results = $query->all();
        foreach ($results as $result) {
            $logs[] = new ScheduleLog($result);
        }

        return $logs;
    }

    /**
     * @param LogCriteria|array|null $criteria
     * @return ScheduleLog|null
     */
    public function findLog($criteria)
    {
        if (!$criteria instanceof LogCriteria) {
            $criteria = new LogCriteria($criteria);
        }

        $criteria->limit = 1;

        $results = $this->findLogs($criteria);
        if (!$results) {
            return null;
        }

        return array_pop($results);
    }

    /**
     * @param LogCriteria|array|null $criteria
     * @return int
     */
    public function getTotalLogs($criteria): int
    {
        if (!$criteria instanceof LogCriteria) {
            $criteria = new LogCriteria($criteria);
        }
        $query = (new Query())->from(['logs' => Table::SCHEDULELOGS]);
        $this->_applyConditions($query, $criteria);

        return $query->count('[[logs.id]]');
    }

    /**
     * @param int $logId
     * @return ScheduleLog|null
     */
    public function getLogById(int $logId)
    {
        $result = $this->_createQuery()
            ->where(['id' => $logId])
            ->one();

        return $result ? new ScheduleLog($result) : null;
    }

    /**
     * @param int $scheduleId
     * @return bool
     */
    public function deleteLogsByScheduleId(int $scheduleId): bool
    {
        Craft::$app->getDb()
            ->createCommand()
            ->delete(Table::SCHEDULELOGS, [
                'scheduleId' => $scheduleId,
            ])
            ->execute();

        return true;
    }

    /**
     * @param Carbon $datetime
     * @return bool
     */
    public function deleteLogsByDateCreated($datetime): bool
    {
        Craft::$app->getDb()
            ->createCommand()
            ->delete(Table::SCHEDULELOGS, [
                '<=', 'dateCreated', $datetime,
            ])
            ->execute();

        return true;
    }

    /**
     * @return bool
     */
    public function deleteAllLogs(): bool
    {
        Craft::$app->getDb()
            ->createCommand()
            ->delete(Table::SCHEDULELOGS)
            ->execute();

        return true;
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
                'logs.id',
                'logs.scheduleId',
                'logs.status',
                'logs.reason',
                'logs.startTime',
                'logs.endTime',
                'logs.output',
                'logs.sortOrder'
            ])
            ->from(['logs' => Table::SCHEDULELOGS]);
    }

    /**
     * @param Query $query
     * @param LogCriteria $criteria
     */
    private function _applyConditions(Query $query, LogCriteria $criteria)
    {
        if ($criteria->scheduleId) {
            $query->andWhere(Db::parseParam('logs.scheduleId', $criteria->scheduleId));
        }

        if ($criteria->schedule) {
            if ($criteria->schedule instanceof Schedule) {
                $query->andWhere(Db::parseParam('logs.scheduleId', $criteria->schedule->id));
            } else {
                $query->leftJoin(Table::SCHEDULES . ' schedules', '[[schedules.id]] = [[logs.scheduleId]]');
                $query->andWhere(Db::parseParam('schedules.handle', $criteria->schedule));
            }
        }
    }
}