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
use craft\web\Request;
use panlatent\craft\actions\abstract\ActionInterface;
use panlatent\craft\actions\abstract\ContextInterface;
use panlatent\schedule\actions\Console;
use panlatent\schedule\actions\CraftConsole;
use panlatent\schedule\actions\ElementAction;
use panlatent\schedule\actions\HttpRequest;
use panlatent\schedule\actions\SendEmail;
use panlatent\schedule\db\Table;
use panlatent\schedule\errors\ActionException;
use panlatent\schedule\events\ActionEvent;
use panlatent\schedule\log\LogAdapter;
use panlatent\schedule\models\Context;
use panlatent\schedule\records\Action as ActionRecord;
use yii\base\Component;

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
                Console::class,
                CraftConsole::class,
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

        return $this->runActionWithContext($action, $context);
    }

    public function runActionWithContext(ActionInterface $action, ContextInterface $context, bool $runValidation = true): bool
    {
//        if ($runValidation && !$action->validate()) {
//            return false;
//        }
        return $action->execute($context);
    }

    public function createAction(mixed $config): ActionInterface
    {
        try {
            return ComponentHelper::createComponent($config, ActionInterface::class);
        } catch (MissingComponentException $exception) {

        }
    }

    public function createActionFromRequest(?Request $request = null): ActionInterface
    {
        if ($request === null) {
            $request = Craft::$app->getRequest();
        }

        $type = $request->getBodyParam('actionType');
        $settings = $request->getBodyParam('actionTypes.' . $type) ?? [];
        return $this->createAction(['type' => $type, 'settings' => $settings]);
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
            if (!$isNew) {
                $record = ActionRecord::findOne(['id' => $action->id]);
                if (!$record) {
                    throw new ActionException("No action exists with the ID: “{$action->id}“.");
                }
            } else {
                $record = new ActionRecord();
            }

            $record->type = get_class($action);
            $record->settings = json_encode($action->getSettings(), JSON_THROW_ON_ERROR);
            $record->save(false);

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        if ($isNew) {
            $action->id = $record->id;
        }

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