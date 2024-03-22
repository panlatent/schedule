<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\migrations;

use craft\db\Migration;

/**
 * m190809_073344_add_schedules_enabled_column migration.
 */
class m190809_073344_add_schedules_enabled_column extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): void
    {
        $this->addColumn('{{%schedules}}', 'enabled', $this->boolean()->notNull()->defaultValue(true)->after('settings'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): void
    {
        $this->dropColumn('{{%schedules}}', 'enabled');
    }
}
