<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\controllers;

use Craft;
use craft\web\Controller;
use panlatent\schedule\Plugin;
use yii\web\Response;

/**
 * Class LogsController
 *
 * @package panlatent\schedule\controllers
 * @author Panlatent <panlatent@gmail.com>
 */
class LogsController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * @return Response
     */
    public function actionDeleteAllLogs(): Response
    {
        $this->requirePostRequest();
        $this->requireAdmin();

        if (!Plugin::getInstance()->getLogs()->deleteAllLogs()) {
            return $this->asJson([
                'success' => false
            ]);
        }

        return $this->asJson([
            'success' => true
        ]);
    }

    /**
     * @return Response
     */
    public function actionDeleteLogsByScheduleId(): Response
    {
        $this->requirePostRequest();

        $schedules = Plugin::getInstance()->getSchedules();

        $scheduleId = Craft::$app->getRequest()->getBodyParam('scheduleId');
        $schedule = $schedules->getScheduleById($scheduleId);
        if (!$schedule) {
            return $this->asJson([
                'success' => false
            ]);
        }

        if (!Plugin::getInstance()->getLogs()->deleteLogsByScheduleId($scheduleId)) {
            return $this->asJson([
                'success' => false
            ]);
        }

        return $this->asJson([
            'success' => true
        ]);
    }
}