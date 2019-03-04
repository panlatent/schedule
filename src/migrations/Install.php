<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\migrations;

use craft\db\Migration;

/**
 * Class Install
 *
 * @package panlatent\schedule\migrations
 * @author Panlatent <panlatent@gmail.com>
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%schedulegroups}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%schedulegroups}}', ['name'], true);

        $this->createTable('{{%schedules}}', [
            'id' => $this->primaryKey(),
            'groupId' => $this->integer(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'description' => $this->string(),
            'type' => $this->string()->notNull(),
            'minute' => $this->string()->notNull()->defaultValue('*'),
            'hour' => $this->string()->notNull()->defaultValue('*'),
            'day' => $this->string()->notNull()->defaultValue('*'),
            'month' => $this->string()->notNull()->defaultValue('*'),
            'week' => $this->string()->notNull()->defaultValue('*'),
            'user' => $this->string(),
            'timer' => $this->string()->notNull(),
            'settings' => $this->text(),
            'sortOrder' => $this->smallInteger()->unsigned(),
            'dateLastStarted' => $this->dateTime(),
            'dateLastFinished' => $this->dateTime(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%schedules}}', ['groupId']);
        $this->createIndex(null, '{{%schedules}}', ['groupId', 'handle'], true);
        $this->createIndex(null, '{{%schedules}}', ['type']);
        $this->createIndex(null, '{{%schedules}}', ['dateCreated']);
        $this->createIndex(null, '{{%schedules}}', ['sortOrder', 'dateCreated']);
        $this->addForeignKey(null, '{{%schedules}}', 'groupId', '{{%schedulegroups}}', 'id', 'SET NULL');

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists('{{%schedules}}');
        $this->dropTableIfExists('{{%schedulegroups}}');

        return true;
    }
}