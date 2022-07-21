<?php

namespace panlatent\schedule\migrations;

use Craft;
use craft\db\Migration;

/**
 * m220721_055944_add_emails_fields migration.
 */
class m220721_055944_add_emails_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%schedules}}', 'emailOnError', $this->boolean()->notNull()->defaultValue(false)->after('enabledLog'));
        $this->addColumn('{{%schedules}}', 'emailOnSuccess', $this->boolean()->notNull()->defaultValue(false)->after('emailOnError'));
        $this->addColumn('{{%schedules}}', 'email', $this->string(255)->after('emailOnSuccess'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%schedules}}', 'emailOnError');
        $this->dropColumn('{{%schedules}}', 'emailOnSuccess');
        $this->dropColumn('{{%schedules}}', 'email');
    }
}
