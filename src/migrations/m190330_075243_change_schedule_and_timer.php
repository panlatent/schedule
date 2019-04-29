<?php

namespace panlatent\schedule\migrations;

use craft\db\Migration;

/**
 * m190330_075243_change_schedule_and_timer migration.
 */
class m190330_075243_change_schedule_and_timer extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%scheduletimers}}', [
            'id' => $this->primaryKey(),
            'scheduleId' => $this->integer()->notNull(),
            'type' => $this->string()->notNull(),
            'minute' => $this->string()->notNull()->defaultValue('*'),
            'hour' => $this->string()->notNull()->defaultValue('*'),
            'day' => $this->string()->notNull()->defaultValue('*'),
            'month' => $this->string()->notNull()->defaultValue('*'),
            'week' => $this->string()->notNull()->defaultValue('*'),
            'settings' => $this->text(),
            'enabled' => $this->boolean()->notNull()->defaultValue(true),
            'sortOrder' => $this->smallInteger()->defaultValue(0),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%scheduletimers}}', 'scheduleId');
        $this->createIndex(null, '{{%scheduletimers}}', 'type');
        $this->createIndex(null, '{{%scheduletimers}}', ['enabled', 'dateCreated']);
        $this->createIndex(null, '{{%scheduletimers}}', ['scheduleId', 'sortOrder']);

        $this->addForeignKey(null, '{{%scheduletimers}}', 'scheduleId', '{{%schedules}}', 'id', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists('{{%scheduletimers}}');
    }
}
