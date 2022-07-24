<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\migrations;

use craft\db\Migration;

/**
 * m190430_082749_advance_schedulelogs_output_type migration.
 */
class m190430_082749_advance_schedulelogs_output_type extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->alterColumn('{{%schedulelogs}}', 'output', $this->mediumText());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->alterColumn('{{%schedulelogs}}', 'output', $this->text());
    }
}
