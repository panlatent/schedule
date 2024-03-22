<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\migrations;

use craft\db\Migration;
use craft\helpers\DateTimeHelper;
use yii\db\Query;

/**
 * m190429_124521_advance_schedules_datetime_fields migration.
 */
class m190429_124521_advance_schedules_datetime_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): void
    {
        $this->addColumn('{{%schedules}}', 'lastStartedTime', $this->bigInteger()->after('settings'));
        $this->addColumn('{{%schedules}}', 'lastFinishedTime', $this->bigInteger()->after('lastStartedTime'));
        $this->addColumn('{{%schedules}}', 'lastStatus', $this->boolean()->after('lastFinishedTime'));

        $results = (new Query())
            ->select(['id', 'dateLastStarted', 'dateLastFinished'])
            ->from('{{%schedules}}')
            ->all();

        foreach ($results as $result) {
            $this->update('{{%schedules}}', [
                'lastStartedTime' => DateTimeHelper::toDateTime($result['dateLastStarted'])->getTimestamp() * 1000,
                'lastFinishedTime' => DateTimeHelper::toDateTime($result['dateLastFinished'])->getTimestamp() * 1000,
            ]);
        }

        $this->dropColumn('{{%schedules}}', 'dateLastStarted');
        $this->dropColumn('{{%schedules}}', 'dateLastFinished');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return false;
    }
}
