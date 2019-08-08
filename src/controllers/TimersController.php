<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2019 panlatent@gmail.com
 */

namespace panlatent\schedule\controllers;

use Craft;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use panlatent\schedule\base\Schedule;
use panlatent\schedule\base\Timer;
use panlatent\schedule\base\TimerInterface;
use panlatent\schedule\Plugin;
use panlatent\schedule\timers\Custom;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class TimersController
 *
 * @package panlatent\schedule\controllers
 * @author Panlatent <panlatent@gmail.com>
 */
class TimersController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * @param int|null $scheduleId
     * @param int|null $timerId
     * @param TimerInterface|null $timer
     * @return Response
     */
    public function actionEditTimer(int $scheduleId = null, int $timerId = null, TimerInterface $timer = null): Response
    {
        /** @var Timer $timer */
        /** @var Schedule $schedule */
        $timers = Plugin::$plugin->getTimers();

        if ($timer === null) {
            $schedule = Plugin::$plugin->getSchedules()->getScheduleById($scheduleId);
            if (!$schedule) {
                throw new NotFoundHttpException();
            }

            if ($timerId !== null) {
                $timer = $timers->getTimerById($timerId);
                if (!$timer) {
                    throw new NotFoundHttpException();
                }
            } else {
                $timer = $timers->createTimer([
                    'type' => Custom::class,
                    'scheduleId' => $scheduleId,
                ]);
            }
        } else {
            $schedule = $timer->getSchedule();
        }

        if (!$schedule) {
            throw new NotFoundHttpException();
        }

        $allTimerTypes = $timers->getAllTimerTypes();

        $timerInstances = [];
        $timerTypeOptions = [];
        foreach ($allTimerTypes as $class) {
            $timerInstances[$class] = $class === get_class($timer) ? $timer : new $class();
            $timerTypeOptions[] = [
                'label' => $class::displayName(),
                'value' => $class,
            ];
        }

        $isNewTimer = !$timer->id;

        $crumbs = [
            ['label' => Craft::t('schedule', 'Schedule'), 'url' => UrlHelper::cpUrl('schedule')],
        ];

        if ($schedule->group) {
            $crumbs[] = ['label' => (string)$schedule->group, 'url' => UrlHelper::cpUrl('schedule/groups/' . $schedule->group->id)];
        }
        $crumbs[] = ['label' => (string)$schedule, 'url' => UrlHelper::cpUrl('schedule/' . $schedule->id)];
        $crumbs[] = ['label' => Craft::t('schedule', 'Timers'), 'url' => UrlHelper::cpUrl('schedule/'. $schedule->id . '/timers')];


        return $this->renderTemplate('schedule/timers/_edit', [
            'timer' => $timer,
            'timerInstances' => $timerInstances,
            'timerTypes' => $allTimerTypes,
            'timerTypeOptions' => $timerTypeOptions,
            'title' => $isNewTimer ? Craft::t('schedule', 'Create a timer') : (string)$timer,
            'crumbs' => $crumbs,
        ]);
    }

    /**
     * Save a timer.
     *
     * @return Response|null
     */
    public function actionSaveTimer()
    {
        $this->requirePostRequest();

        $timers = Plugin::$plugin->getTimers();
        $request = Craft::$app->getRequest();

        $type = $request->getBodyParam('type');

        $timer = $timers->createTimer([
            'id' => $request->getBodyParam('timerId'),
            'type' => $type,
            'scheduleId' => $request->getBodyParam('scheduleId'),
            'minute' => $request->getBodyParam('minute'),
            'hour' => $request->getBodyParam('hour'),
            'day' => $request->getBodyParam('day'),
            'month' => $request->getBodyParam('month'),
            'week' => $request->getBodyParam('week'),
            'enabled' => (bool)$request->getBodyParam('enabled'),
            'settings' => $request->getBodyParam('types.' . $type)
        ]);

        if (!$timers->saveTimer($timer)) {
            Craft::$app->getSession()->setError(Craft::t('schedule', 'Couldn’t save timer.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'timer' => $timer,
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('schedule', 'Timer saved.'));

        return $this->redirectToPostedUrl();
    }

    /**
     * Delete a timer.
     *
     * @return Response
     */
    public function actionDeleteTimer(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $timers = Plugin::$plugin->getTimers();

        $timerId = Craft::$app->getRequest()->getBodyParam('id');
        $timer = $timers->getTimerById($timerId);
        if (!$timer) {
            throw new NotFoundHttpException();
        }

        if (!$timers->deleteTimer($timer)) {
            return $this->asJson([
                'success' => false,
            ]);
        }

        return $this->asJson([
            'success' => true,
        ]);
    }

    /**
     * Reorder all schedule timers.
     *
     * @return Response
     */
    public function actionReorderTimers(): Response
    {
        $this->requirePostRequest();

        $ids = Craft::$app->getRequest()->getBodyParam('ids');
        $ids = Json::decodeIfJson($ids);

        return $this->asJson([
            'success' => Plugin::$plugin->getTimers()->reorderTimers($ids)
        ]);
    }
}