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
use panlatent\schedule\schedules\HttpRequest;
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
     * Edit a schedule.
     *
     * @param int|null $scheduleId
     * @param ScheduleInterface|null $schedule
     * @return Response
     */
    public function actionEditSchedule(int $scheduleId = null, ScheduleInterface $schedule = null): Response
    {
        $schedules = Plugin::$plugin->getSchedules();

        /** @var Schedule $schedule */
        if ($schedule === null) {
            if ($scheduleId !== null) {
                $schedule = $schedules->getScheduleById($scheduleId);
                if (!$schedule) {
                    throw new NotFoundHttpException();
                }
            } else {
                $schedule = $schedules->createSchedule(HttpRequest::class);
            }
        }

        $isNewSchedule = $schedule->getIsNew();

        $allGroups = $schedules->getAllGroups();
        $allScheduleTypes = $schedules->getAllScheduleTypes();

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

        return $this->renderTemplate('schedule/_edit', [
            'isNewSchedule' => $isNewSchedule,
            'groupOptions' => $groupOptions,
            'schedule' => $schedule,
            'scheduleInstances' => $scheduleInstances,
            'scheduleTypes' => $allScheduleTypes,
            'scheduleTypeOptions' => $scheduleTypeOptions,
        ]);
    }

    /**
     * @param string $scheduleType
     * @return Response
     */
    public function actionGetScheduleSettingsHtml(string $scheduleType): Response
    {
        if (!class_exists($scheduleType)) {
            return $this->asErrorJson("schedule class $scheduleType not found");
        }

        /** @var Schedule $schedule */
        $schedule = new $scheduleType;

        $view = Craft::$app->getView();
        $view->startJsBuffer();
        $view->startCssBuffer();
        $html =$schedule->getSettingsHtml();
        $js = $view->clearJsBuffer();
        $css = $view->clearCssBuffer();

        return $this->asJson([
            'html' => $html,
            'js' => $js,
            'css' => $css,
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

        $schedules = Plugin::$plugin->getSchedules();

        /** @var Schedule $schedule */
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
            var_dump($schedule->getErrors());
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