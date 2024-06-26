<?php

namespace panlatent\schedule\controllers;

use Craft;
use craft\elements\Entry;
use craft\web\Controller;
use panlatent\craft\actions\abstract\ActionInterface;
use panlatent\schedule\actions\HttpRequest;
use panlatent\schedule\log\LogAdapter;
use panlatent\schedule\log\MemoryLog;
use panlatent\schedule\models\Context;
use panlatent\schedule\Plugin;
use panlatent\schedule\utilities\ActionRunner;
use Symfony\Component\Stopwatch\Stopwatch;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ActionsController extends Controller
{
    public function actionEdit(?int $actionId = null, ?ActionInterface $action = null): Response
    {
        if ($actionId !== null) {
            if ($action === null) {
                $action = Plugin::getInstance()->actions->getActionById($actionId);

                if (!$action) {
                    throw new NotFoundHttpException('action not found');
                }
            }

            $title = trim($action->name) ?: Craft::t('schedule', 'Edit Action');
        } else {
            if ($action === null) {
                $action = new HttpRequest();
            }

            $title = Craft::t('schedule', 'Create a new action');
        }

        $allActionTypes = Plugin::getInstance()->actions->getAllActionTypes();
        $actionTypeOptions = [];
        foreach ($allActionTypes as $class) {
            $actionTypeOptions[] = [
                'label' => $class::displayName(),
                'value' => $class,
            ];
        }

        return $this->asCpScreen()
            ->title($title)
            ->addCrumb(Craft::t('app', 'Settings'), 'settings')
            ->addCrumb(Craft::t('app', 'Entry Types'), 'settings/entry-types')
            ->action('schedule/actions/save')
            ->redirectUrl('schedule/actions')
            ->addAltAction(Craft::t('app', 'Save and continue editing'), [
                'redirect' => 'settings/entry-types/{id}',
                'shortcut' => true,
                'retainScroll' => true,
            ])
            ->contentTemplate('schedule/actions/_edit.twig', [
                'actionId' => $actionId,
                'action' => $action,
                'typeName' => $action::displayName(),
                'actionTypes' => $allActionTypes,
                'actionTypeOptions' => $actionTypeOptions,
            ]);
    }

    public function actionRenderSettings(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $type = $this->request->getRequiredBodyParam('type');
        $action = Plugin::getInstance()->actions->createAction($type);

        $view = Craft::$app->getView();
        $html = $view->renderTemplate('schedule/_includes/forms/actionSetting.twig', ['action' => $action]);

        return $this->asJson([
            'settingsHtml' => $html,
            'headHtml' => $view->getHeadHtml(),
            'bodyHtml' => $view->getBodyHtml(),
        ]);
    }

    public function actionRun()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $view = Craft::$app->getView();

        $action = Plugin::getInstance()->actions->createActionFromRequest();
        $logger = new MemoryLog();
        $context = new Context($logger);

        $stopwatch = new Stopwatch();
        $stopwatch->start('run');
        $success = Plugin::getInstance()->actions->runActionWithContext($action, $context);
        $stopwatch->stop('run');

        $html = $context->getOutput()->render();
        $duration = $stopwatch->getEvent('run')->getDuration();
        $memory = $stopwatch->getEvent('run')->getMemory();

        return $this->asJson([
            'success' => $success,
            'outputHtml' => $html,
            'headHtml' => $view->getHeadHtml(),
            'bodyHtml' => $view->getBodyHtml(),
            'logs' => $logger->getMessages(),
            'duration' => $duration >= 1000 ? $duration/1000 . 's' : $duration . 'ms',
            'usageMemory' => $memory >= 1024*1024 ? $memory/(1024*1024) . 'MB' : $memory/1024 . 'KB',
        ]);
    }
}