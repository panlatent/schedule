<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\migrations;

use craft\db\Migration;
use craft\helpers\Db;

/**
 * m191010_060704_fix_shedules_handle_unqidx migration.
 */
class m191010_060704_fix_shedules_handle_unqidx extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): void
    {
        Db::dropIndexIfExists('{{%schedules}}', ['groupId', 'handle'], true, $this->db);
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
