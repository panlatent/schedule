<?php

namespace panlatent\schedule\migrations;

use craft\db\Migration;
use yii\db\Query;

/**
 * m190330_080123_move_timers_from_schedules migration.
 */
class m190330_080123_move_timers_from_schedules extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $schedules = (new Query())
            ->select(['id', 'timer', 'minute', 'hour', 'day', 'month', 'week'])
            ->from('{{%schedules}}')
            ->all();

        foreach ($schedules as $schedule) {
            $this->insert('{{%scheduletimers}}', [
                'scheduleId' => $schedule['id'],
                'type' => $schedule['timer'],
                'minute' => $schedule['minute'],
                'hour' => $schedule['hour'],
                'day' => $schedule['day'],
                'month' => $schedule['month'],
                'week' => $schedule['week'],
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->delete('{{%scheduletimers}}');
    }
}
