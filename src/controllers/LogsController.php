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
use panlatent\schedule\models\ScheduleLog;
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
    // Properties
    // =========================================================================

    /**
     * @var bool
     */
    public $enableCsrfValidation = false;

    // Public Methods
    // =========================================================================

    /**
     * @return Response
     */
    public function actionGetLogs(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $logs = Plugin::$plugin->getLogs();
        $data = Craft::$app->getRequest()->getRawBody();
        $params = Json::decodeIfJson($data);

        $criteria = $params['criteria'] ?? [];
        if (!isset($criteria['sortOrder']) || $criteria['sortOrder'] == '') {
            $criteria['sortOrder'] = 'logs.sortOrder DESC';
        }

        $data = $logs->findLogs($criteria);

        return $this->asJson([
            'success' => true,
            'total' => $logs->getTotalLogs($criteria),
            'data' => array_map(function(ScheduleLog $ret) {
                return [
                    'id' => $ret->id,
                    'status' => $ret->status,
                    'reason' => $ret->reason,
                    'startTime' => date('Y-m-d H:i:s', (int)($ret->startTime/1000)) . '.' . $ret->startTime%1000,
                    'endTime' => date('Y-m-d H:i:s', (int)($ret->endTime/1000)) . '.' . $ret->endTime%1000,
                    'duration' => $ret->duration,
                    'sortOrder' => $ret->sortOrder,
                    'output' => $ret->output,
                ];
            }, $data),
        ]);
    }

    /**
     * @return Response
     */
    public function actionGetSchedules(): Response
    {
        $this->requireAcceptsJson();

        $data = Plugin::$plugin->getSchedules()->findSchedules([
            'hasLogs' => true,
        ]);

        return $this->asJson([
            'success' => true,
            'data' => array_map(function(Schedule $ret) {
                return [
                    'id' => $ret->id,
                    'handle' => $ret->handle,
                    'name' => $ret->name,
                    'total' => $ret->totalLogs,
                    'duration' => $ret->lastDuration,
                    'finished' => $ret->lastFinishedDate ? $ret->lastFinishedDate->format('Y-m-d H:i:s') : null,
                    'status' => $ret->lastStatus !== null ? (bool)$ret->lastStatus : null,
                    'logsUri' => UrlHelper::actionUrl('schedule/logs/' . $ret->handle),
                ];
            }, $data),
        ]);
    }
}