<?php

namespace panlatent\schedule\migrations;

use craft\db\Migration;

/**
 * m190423_161829_schedulelogs migration.
 */
class m190423_161829_schedulelogs extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%schedulelogs}}', [
            'id' => $this->primaryKey(),
            'scheduleId' => $this->integer()->notNull(),
            'status' => $this->string()->notNull(),
            'attempts' => $this->tinyInteger(3),
            'settings' => $this->text(),
            'trigger' => $this->string(),
            'startTime' => $this->bigInteger(),
            'endTime' => $this->bigInteger(),
            'output' => $this->text(),
            'sortOrder' => $this->integer()->defaultValue(0),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%schedulelogs}}', 'scheduleId');
        $this->createTable(null, '{{%schedulelogs}}', ['scheduleId', 'sortOrder']);
        $this->addForeignKey(null, '{{%schedulelogs}}', 'scheduleId', '{{%schedules}}', 'id', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%schedulelogs}}');
    }
}
