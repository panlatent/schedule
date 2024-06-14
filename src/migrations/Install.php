<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
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
    public function safeUp(): bool
    {
        $this->createTable('{{%schedule_actions}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string()->notNull(),
            'settings' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('{{%schedule_schedulegroups}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'static' => $this->boolean()->defaultValue(false),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
        $this->createIndex(null, '{{%schedule_schedulegroups}}', ['name'], true);

        $this->createTable('{{%schedule_schedules}}', [
            'id' => $this->primaryKey(),
            'groupId' => $this->integer(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'description' => $this->string(),
            // 'static' => $this->boolean()->defaultValue(false),
            'actionId' => $this->integer()->notNull(),
            'onSuccess' => $this->integer(),
            'onFailed' => $this->integer(),
            //'enabledLog' => $this->boolean()->defaultValue(false),
            'enabled' => $this->boolean()->notNull()->defaultValue(true),
            'sortOrder' => $this->smallInteger()->unsigned(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
        $this->createIndex(null, '{{%schedule_schedules}}', 'groupId');
        $this->createIndex(null, '{{%schedule_schedules}}', 'handle', true);
        $this->createIndex(null, '{{%schedule_schedules}}', 'dateCreated');
        $this->createIndex(null, '{{%schedule_schedules}}', ['sortOrder', 'dateCreated']);
        $this->addForeignKey(null, '{{%schedule_schedules}}', 'actionId', '{{%schedule_actions}}', 'id');
        $this->addForeignKey(null, '{{%schedule_schedules}}', 'onSuccess', '{{%schedule_actions}}', 'id', 'SET NULL');
        $this->addForeignKey(null, '{{%schedule_schedules}}', 'onFailed', '{{%schedule_actions}}', 'id', 'SET NULL');

        $this->createTable('{{%schedule_scheduletraces}}', [
            'id' => $this->primaryKey(),
            'scheduleId' => $this->integer()->notNull(),
            'traces' => $this->text(),
//            'firstStartedTime' =>  $this->bigInteger(),
//            'lastStartedTime' =>  $this->bigInteger(),
//            'lastFinishedTime' =>  $this->bigInteger(),
//            'lastStatus' => $this->boolean(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
        $this->addForeignKey(null, '{{%schedule_scheduletraces}}', 'scheduleId', '{{%schedule_schedules}}', 'id', 'CASCADE');

        $this->createTable('{{%schedule_timers}}', [
            'id' => $this->primaryKey(),
            'scheduleId' => $this->integer()->notNull(),
            'type' => $this->string()->notNull(),
            'settings' => $this->text(),
            'enabled' => $this->boolean()->notNull()->defaultValue(true),
            'sortOrder' => $this->smallInteger()->defaultValue(0),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('{{%schedule_tasks}}', [
            'id' => $this->primaryKey(),
            'scheduleId' => $this->integer()->notNull(),
            'status' => $this->string()->notNull(),
            'attempts' => $this->tinyInteger(3),
            'reason' => $this->string(),
            'trigger' => $this->string(),
            'startTime' => $this->bigInteger(),
            'endTime' => $this->bigInteger(),
            'output' => $this->mediumText(),
            'sortOrder' => $this->integer()->defaultValue(0),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
        $this->createIndex(null, '{{%schedule_tasks}}', 'scheduleId');
        $this->createIndex(null, '{{%schedule_tasks}}', ['scheduleId', 'sortOrder']);
        $this->addForeignKey(null, '{{%schedule_tasks}}', 'scheduleId', '{{%schedule_schedules}}', 'id', 'CASCADE');

        $this->createTable('{{%schedule_scheduler}}', [
            'id' => $this->primaryKey(),
            'instanceId' => $this->string()->notNull(),
            'key' => $this->string()->notNull(),
            'value' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): void
    {
        $this->dropTableIfExists('{{%schedule_scheduler}}');
        $this->dropTableIfExists('{{%schedule_tasks}}');
        $this->dropTableIfExists('{{%schedule_timers}}');
        $this->dropTableIfExists('{{%schedule_scheduletraces}}');
        $this->dropTableIfExists('{{%schedule_schedules}}');
        $this->dropTableIfExists('{{%schedule_schedulegroups}}');
        $this->dropTableIfExists('{{%schedule_actions}}');
    }
}