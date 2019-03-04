<?php

namespace panlatent\schedule\migrations;

use craft\db\Migration;

/**
 * m190303_143914_add_schedules_timer_column migration.
 */
class m190303_143914_add_schedules_timer_column extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%schedules}}', 'timer', $this->string()->notNull()->after('user'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%schedules}}', 'timer');
    }
}
