<?php

namespace panlatent\schedule\migrations;

use craft\db\Migration;
use craft\helpers\MigrationHelper;

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
        MigrationHelper::dropIndexIfExists('{{%schedules}}', ['groupId', 'handle'], true, $this);
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
