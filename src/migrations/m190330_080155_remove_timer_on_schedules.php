<?php

namespace panlatent\schedule\migrations;

use craft\db\Migration;

/**
 * m190330_080155_remove_timer_on_schedules migration.
 */
class m190330_080155_remove_timer_on_schedules extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropColumn('{{%schedules}}', 'timer');
        $this->dropColumn('{{%schedules}}', 'minute');
        $this->dropColumn('{{%schedules}}', 'hour');
        $this->dropColumn('{{%schedules}}', 'day');
        $this->dropColumn('{{%schedules}}', 'month');
        $this->dropColumn('{{%schedules}}', 'week');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addColumn('{{%schedules}}', 'timer', $this->string()->notNull()->after('user'));
        $this->addColumn('{{%schedules}}', 'week', $this->string()->notNull()->defaultValue('*')->after('type'));
        $this->addColumn('{{%schedules}}', 'month', $this->string()->notNull()->defaultValue('*')->after('type'));
        $this->addColumn('{{%schedules}}', 'day', $this->string()->notNull()->defaultValue('*')->after('type'));
        $this->addColumn('{{%schedules}}', 'hour', $this->string()->notNull()->defaultValue('*')->after('type'));
        $this->addColumn('{{%schedules}}', 'minute', $this->string()->notNull()->defaultValue('*')->after('type'));
    }
}
