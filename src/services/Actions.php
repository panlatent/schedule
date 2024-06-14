<?php

namespace panlatent\schedule\services;

use Craft;
use craft\db\Query;
use craft\errors\MissingComponentException;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\ArrayHelper;
use craft\helpers\Component as ComponentHelper;
use craft\helpers\Db;
use craft\helpers\StringHelper;
use panlatent\craft\actions\abstract\ActionInterface;
use panlatent\schedule\actions\Command;
use panlatent\schedule\actions\Console;
use panlatent\schedule\actions\ElementAction;
use panlatent\schedule\actions\HttpRequest;
use panlatent\schedule\actions\SendEmail;
use panlatent\schedule\db\Table;
use panlatent\schedule\events\ActionEvent;
use panlatent\schedule\log\LogAdapter;
use panlatent\schedule\models\Context;
use yii\base\Component;
use yii\web\Request;

class Actions extends Component
{
    public const EVENT_REGISTER_ACTION_TYPES = 'registerActionTypes';

    /**
     * @event ActionEvent
     */
    public const EVENT_BEFORE_SAVE_ACTION = 'beforeSaveAction';

    /**
     * @event ActionEvent
     */
    public const EVENT_AFTER_SAVE_ACTION = 'afterSaveAction';

    /**
     * @event ActionEvent
     */
    public const EVENT_BEFORE_DELETE_ACTION = 'beforeDeleteAction';

    /**
     * @event ActionEvent
     */
    public const EVENT_AFTER_DELETE_ACTION = 'afterDeleteAction';

    /**
     * @var ActionInterface[]
     */
    private ?array $actions = null;

    public function getAllActionTypes(): array
    {
        $event = new RegisterComponentTypesEvent([
            'types' => [
                Command::class,
                Console::class,
                ElementAction::class,
                HttpRequest::class,
                SendEmail::class,
            ]
        ]);

        $this->trigger(self::EVENT_REGISTER_ACTION_TYPES, $event);

        return $event->types;
    }

    /**
     * @return ActionInterface[]
     */
    public function getAllActions(): array
    {
        if ($this->actions === null) {
            $this->actions = [];
            $rows = $this->createQuery()->all();
            foreach ($rows as $row) {
                $action = $this->createAction($row);
                $this->actions[] = $action;
            }
        }

        return $this->actions;
    }

    public function getActionById(int $actionId)
    {
        return ArrayHelper::firstWhere($this->getAllActions(), 'id', $actionId);
    }

    public function runAction(ActionInterface $action): bool
    {
        $context = new Context(new LogAdapter(Craft::$app->getLog()->getLogger(), 'action'));


        return $action->execute($context);
    }

    public function createAction(mixed $config): ActionInterface
    {
        try {
            return ComponentHelper::createComponent($config, ActionInterface::class);
        } catch (MissingComponentException $exception) {

        }
    }

    public function saveAction(ActionInterface $action, bool $runValidation = true): bool
    {
        $isNew = $action->getIsNew();

        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_ACTION)) {
            $this->trigger(self::EVENT_BEFORE_SAVE_ACTION, new ActionEvent([
                'action' => $action,
                'isNew' => $isNew,
            ]));
        }

        if (!$action->beforeSave($isNew)) {
            return false;
        }

        if ($isNew) {
            $action->uid = StringHelper::UUID();
        } elseif ($action->uid === null) {
            $action->uid = Db::uidById(Table::ACTIONS, $action->id);
        }

        if ($runValidation && !$action->validate()) {
            Craft::info('Action not saved due to validation error.', __METHOD__);
            return false;
        }

        $transaction = Craft::$app->getDb()->beginTransaction();
        try {
            Craft::$app->getDb()
                ->createCommand()
                ->upsert(Table::ACTIONS, [
                    'type' => get_class($action),
                    'settings' => $action->getSettings(),
                    'dateUpdated' => $action->dateUpdated,
                    'dateCreated' => $action->dateCreated,
                    'uid' => $action->uid,
                ], [
                    'type' => get_class($action),
                    'settings' => $action->getSettings(),
                    'dateUpdated' => $action->dateUpdated,
                ])
                ->execute();

            if ($isNew) {
                $action->id = $record->id;
            }

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        // If has some respositories ...
        // $this->__METHOD__[$action->id] = $action;
        // $this->__METHOD__[$action->handle] = $action;

        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_ACTION)) {
            $this->trigger(self::EVENT_AFTER_SAVE_ACTION, new ActionEvent([
                'action' => $action,
                'isNew' => $isNew,
            ]));
        }

        $action->afterSave($isNew);

        return true;
    }

    private function createQuery(): Query
    {
        return (new Query())
            ->select([
                'actions.id',
                'actions.type',
                'actions.settings',
                'actions.dateCreated',
                'actions.dateUpdated',
                'actions.uid',
            ])
            ->from(['actions' => Table::ACTIONS]);
    }
}