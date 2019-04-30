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
        // Schedule Groups
        $this->createTable('{{%schedulegroups}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%schedulegroups}}', ['name'], true);

        // Schedules
        $this->createTable('{{%schedules}}', [
            'id' => $this->primaryKey(),
            'groupId' => $this->integer(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'description' => $this->string(),
            'type' => $this->string()->notNull(),
            'user' => $this->string(),
            'settings' => $this->text(),
            'enabledLog' => $this->boolean()->defaultValue(false),
            'lastStartedTime' =>  $this->bigInteger(),
            'lastFinishedTime' =>  $this->bigInteger(),
            'lastStatus' => $this->boolean(),
            'sortOrder' => $this->smallInteger()->unsigned(),
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

        // Schedule Timers
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
        $this->addForeignKey(null, '{{%scheduletimers}}', 'scheduleId', '{{%schedule}}', 'id', 'CASCADE');

        // Schedule Logs
        $this->createTable('{{%schedulelogs}}', [
            'id' => $this->primaryKey(),
            'scheduleId' => $this->integer()->notNull(),
            'status' => $this->string()->notNull(),
            'attempts' => $this->tinyInteger(3),
            'settings' => $this->text(),
            'trigger' => $this->string(),
            'startTime' => $this->bigInteger(),
            'endTime' => $this->bigInteger(),
            'output' => $this->mediumText(),
            'sortOrder' => $this->integer()->defaultValue(0),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%schedulelogs}}', 'scheduleId');
        $this->createIndex(null, '{{%schedulelogs}}', ['scheduleId', 'sortOrder']);
        $this->addForeignKey(null, '{{%schedulelogs}}', 'scheduleId', '{{%schedules}}', 'id', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists('{{%schedulelogs}}');
        $this->dropTableIfExists('{{%scheduletimers}}');
        $this->dropTableIfExists('{{%schedules}}');
        $this->dropTableIfExists('{{%schedulegroups}}');
    }
}