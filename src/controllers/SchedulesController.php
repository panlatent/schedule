<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\controllers;

use Craft;
use craft\helpers\Json;
use craft\web\Controller;
use panlatent\schedule\base\Schedule;
use panlatent\schedule\base\ScheduleInterface;
use panlatent\schedule\models\ScheduleGroup;
use panlatent\schedule\Plugin;
use panlatent\schedule\schedules\Console;
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
    /**
     * @return Response
     */
    public function actionSaveGroup(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $schedules = Plugin::$plugin->getSchedules();

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

        Craft::$app->getSession()->setName(Craft::t('schedule', 'Group saved.'));

        return $this->asJson([
            'success' => true,
            'group' => $group,
        ]);
    }

    /**
     * @return Response
     */
    public function actionDeleteGroup(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $schedules = Plugin::$plugin->getSchedules();
        $groupId = Craft::$app->getRequest()->getBodyParam('id');

        $group = $schedules->getGroupById($groupId);
        if (!$group) {
            throw new NotFoundHttpException();
        }

        if (!$schedules->deleteGroup($group)) {
            return $this->asErrorJson(Craft::t('app', 'Couldn’t delete group.', ['name' => $group->name]));
        }

        return $this->asJson([
            'success' => true,
        ]);
    }

    /**
     * @param int|null $scheduleId
     * @param ScheduleInterface|null $schedule
     * @return Response
     */
    public function actionEditSchedule(int $scheduleId = null, ScheduleInterface $schedule = null): Response
    {
        $schedules = Plugin::$plugin->getSchedules();
        $timers = Plugin::$plugin->getTimers();

        /** @var Schedule $schedule */
        if ($schedule === null) {
            if ($scheduleId !== null) {
                $schedule = $schedules->getScheduleById($scheduleId);
                if (!$schedule) {
                    throw new NotFoundHttpException();
                }
            } else {
                $schedule = $schedules->createSchedule(Console::class);
            }
        }

        $isNewSchedule = $schedule->getIsNew();

        $allGroups = $schedules->getAllGroups();
        $allScheduleTypes = $schedules->getAllScheduleTypes();
        $allTimerTypes = $timers->getAllTimerTypes();

        $groupOptions = [
            [
                'label' => Craft::t('schedule', 'Ungrouped'),
                'value' => '',
            ]
        ];

        foreach ($allGroups as $group) {
            $groupOptions[] = [
                'label' => $group->name,
                'value' => $group->id,
            ];
        }

        $scheduleInstances = [];
        $scheduleTypeOptions = [];
        foreach ($allScheduleTypes as $class) {
            /** @var ScheduleInterface|string $class */
            $scheduleInstances[$class] = new $class();
            $scheduleTypeOptions[] = [
                'label' => $class::displayName(),
                'value' => $class,
            ];
        }

        $timerInstances = [];
        $timerTypeOptions = [];
        foreach ($allTimerTypes as $class) {
            $timerInstances[$class] = new $class([
                'minute' => $schedule->minute,
                'hour' => $schedule->hour,
                'day' => $schedule->day,
                'month' => $schedule->month,
                'week' => $schedule->week,
            ]);
            $timerTypeOptions[] = [
                'label' => $class::displayName(),
                'value' => $class,
            ];
        }

        return $this->renderTemplate('schedule/_edit', [
            'isNewSchedule' => $isNewSchedule,
            'groupOptions' => $groupOptions,
            'schedule' => $schedule,
            'scheduleInstances' => $scheduleInstances,
            'scheduleTypes' => $allScheduleTypes,
            'scheduleTypeOptions' => $scheduleTypeOptions,
            'timerInstances' => $timerInstances,
            'timerTypes' => $allTimerTypes,
            'timerTypeOptions' => $timerTypeOptions,
        ]);
    }

    /**
     * Save a schedule.
     *
     * @return Response|null
     */
    public function actionSaveSchedule()
    {
        $this->requirePostRequest();

        $schedules = Plugin::$plugin->getSchedules();
        $timers = Plugin::$plugin->getTimers();
        $request = Craft::$app->getRequest();

        $type = $request->getBodyParam('type');
        $timerType = $request->getBodyParam('timerType');

        $timer = $timers->createTimer([
            'type' => $timerType,
            'settings' => $request->getBodyParam('timers.' . $timerType)
        ]);

        /** @var Schedule $schedule */
        $schedule = $schedules->createSchedule([
            'id' => $request->getBodyParam('scheduleId'),
            'groupId' =>  $request->getBodyParam('groupId'),
            'name' => $request->getBodyParam('name'),
            'handle' => $request->getBodyParam('handle'),
            'description' => $request->getBodyParam('description'),
            'type' => $type,
            'minute' => $timer->getMinute(),
            'hour' => $timer->getHour(),
            'day' => $timer->getDay(),
            'month' => $timer->getMonth(),
            'week' => $timer->getWeek(),
            'timer' => $timerType,
            'settings' => $request->getBodyParam('types.' . $type, [])
        ]);

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