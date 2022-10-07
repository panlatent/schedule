<?php

namespace panlatent\schedule\migrations;

use Craft;
use craft\db\Migration;

/**
 * m220723_043446_add_notification_channel migration.
 */
class m220723_043446_add_notification_channel extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Schedule Timers
        $this->createTable('{{%schedulenotifications}}', [
            'id' => $this->primaryKey(),
            'scheduleId' => $this->integer()->notNull(),
            'handle' => $this->string()->notNull(),
            'settings' => $this->text(),
            'enabled' => $this->boolean()->notNull()->defaultValue(true),
            'sortOrder' => $this->smallInteger()->defaultValue(0),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
        $this->addForeignKey(null, '{{%schedulenotifications}}', 'scheduleId', '{{%schedules}}', 'id', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists('{{%schedulenotifications}}');
    }
}
