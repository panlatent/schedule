<?php

namespace panlatent\schedule\migrations;

use craft\db\Migration;

/**
 * m240322_025601_static_schedules migration.
 */
class m240322_025601_static_schedules extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->addColumn('{{%schedules}}', 'static', $this->boolean()->defaultValue(false)->after('settings'));
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropColumn('{{%schedules}}', 'static');
        return true;
    }
}
