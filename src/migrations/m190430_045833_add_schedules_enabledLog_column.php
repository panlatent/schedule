<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\migrations;

use craft\db\Migration;

/**
 * m190430_045833_add_schedules_enabledLog_column migration.
 */
class m190430_045833_add_schedules_enabledLog_column extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): void
    {
        $this->addColumn('{{%schedules}}', 'enabledLog', $this->boolean()->defaultValue(false)->after('settings'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): void
    {
        $this->dropColumn('{{%schedules}}', 'enabledLog');
    }
}
