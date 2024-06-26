<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\controllers;

use Craft;
use craft\helpers\Json;
use craft\web\Controller;
use panlatent\schedule\actions\HttpRequest;
use panlatent\schedule\models\Schedule;
use panlatent\schedule\models\ScheduleGroup;
use panlatent\schedule\Plugin;
use panlatent\schedule\timers\Cron;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class SchedulesController
 *
 * @package panlatent\schedule\controllers
 * @author Panlatent <panlatent@gmail.com>
 */
class SchedulesController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Save a schedule group.
     *
     * @return Response
     */
    public function actionSaveGroup(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $schedules = Plugin::getInstance()->schedules;

        $groupId = Craft::$app->getRequest()->getBodyParam('id');
        $groupName = Craft::$app->getRequest()->getBodyParam('name');

        $group = new ScheduleGroup([
            'id' => $groupId,
            'name' => $groupName,
        ]);

        if (!$schedules->saveGroup($group)) {
            return $this->asJson([
                'success' => false,
                'errors' => $group->getErrors(),
            ]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('schedule', 'Group saved.'));

        return $this->asJson([
            'success' => true,
            'group' => $group,
        ]);
    }

    /**
     * Delete a schedule group.
     *
     * @return Response
     */
    public function actionDeleteGroup(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $schedules = Plugin::getInstance()->schedules;
        $groupId = Craft::$app->getRequest()->getBodyParam('id');

        $group = $schedules->getGroupById($groupId);
        if (!$group) {
            throw new NotFoundHttpException();
        }

        if (!$schedules->deleteGroup($group)) {
            return $this->asFailure(Craft::t('app', 'Couldn’t delete group.', ['name' => $group->name]));
        }

        return $this->asJson([
            'success' => true,
        ]);
    }

    /**
     * Edit a schedule.
     *
     * @param int|null $scheduleId
     * @param Schedule|null $schedule
     * @return Response
     */
    public function actionEditSchedule(int $scheduleId = null, Schedule $schedule = null): Response
    {
        $schedules = Plugin::getInstance()->schedules;

        if ($schedule === null) {
            if ($scheduleId !== null) {
                $schedule = $schedules->getScheduleById($scheduleId);
                if (!$schedule) {
                    throw new NotFoundHttpException();
                }
            } else {
                $schedule = new Schedule();
                $schedule->timer = new Cron();
                $schedule->action = new HttpRequest();
            }
        }

        $isNewSchedule = !$schedule->id;

        $allGroups = $schedules->getAllGroups();
        $allActionTypes = Plugin::getInstance()->actions->getAllActionTypes();
        $allTimerTypes = Plugin::getInstance()->timers->getAllTimerTypes();

        $groupOptions = [
            [
                'label' => Craft::t('schedule', 'Ungrouped'),
                'value' => '',
            ],
        ];

        foreach ($allGroups as $group) {
            $groupOptions[] = [
                'label' => $group->name,
                'value' => $group->id,
            ];
        }

        return $this->renderTemplate('schedule/_edit', [
            'isNewSchedule' => $isNewSchedule,
            'groupOptions' => $groupOptions,
            'schedule' => $schedule,
            'actionTypes' => $allActionTypes,
            'actionTypeOptions' => array_map(static fn($class) => ['label' => $class::displayName(), 'value' => $class], $allActionTypes),
            'timerTypes' => $allTimerTypes,
            'timerTypeOptions' => array_map(static fn($class) => ['label' => $class::displayName(), 'value' => $class], $allTimerTypes),
        ]);
    }

    /**
     * Save a schedule.
     *
     * @return Response|null
     */
    public function actionSaveSchedule(): ?Response
    {
        $this->requirePostRequest();

        $schedules = Plugin::getInstance()->schedules;

        $schedule = $schedules->createScheduleFromRequest();

        if (!$schedules->saveSchedule($schedule)) {
            Craft::$app->getSession()->setError(Craft::t('schedule', 'Couldn’t save schedule.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'schedule' => $schedule,
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('schedule', 'Schedule saved.'));

        return $this->redirect('schedule' . ($schedule->groupId ? '/groups/' . $schedule->groupId : ''));
    }

    /**
     * @return Response
     */
    public function actionToggleSchedule(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $schedules = Plugin::$plugin->getSchedules();
        $request = Craft::$app->getRequest();

        $schedule = $schedules->getScheduleById($request->getBodyParam('id'));
        if (!$schedule) {
            return $this->asJson(['success' => false]);
        }
        /** @var Schedule $schedule */
        $schedule->enabled = (bool)$request->getBodyParam('enabled');

        if (!$schedules->saveSchedule($schedule)) {
            return $this->asJson(['success' => false]);
        }

        return $this->asJson(['success' => true]);
    }

    /**
     * @return Response
     */
    public function actionReorderSchedules(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $scheduleIds = Json::decode(Craft::$app->getRequest()->getRequiredBodyParam('ids'));
        Plugin::$plugin->getSchedules()->reorderSchedules($scheduleIds);

        return $this->asJson(['success' => true]);
    }

    /**
     * Delete a schedule.
     *
     * @return Response
     */
    public function actionDeleteSchedule(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $schedules = Plugin::$plugin->getSchedules();

        $scheduleId = Craft::$app->getRequest()->getBodyParam('id');
        $schedule = $schedules->getScheduleById($scheduleId);
        if (!$schedule) {
            throw new NotFoundHttpException();
        }

        if (!$schedules->deleteSchedule($schedule)) {
            return $this->asJson([
                'success' => false,
            ]);
        }

        return $this->asJson([
            'success' => true,
        ]);
    }
}