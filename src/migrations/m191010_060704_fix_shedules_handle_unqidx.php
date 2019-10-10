<?php

namespace panlatent\schedule\migrations;

use craft\db\Migration;

/**
 * m191010_060704_fix_shedules_handle_unqidx migration.
 */
class m191010_060704_fix_shedules_handle_unqidx extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropIndex('schedules_groupId_handle_unq_idx', '{{%schedules}}');
        $this->createIndex(null, '{{%schedules}}', 'handle', true);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return false;
    }
}
