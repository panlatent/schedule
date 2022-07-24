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
use craft\web\View;
use panlatent\schedule\Plugin;
use panlatent\schedule\base\NotificationInterface;
use panlatent\schedule\base\Schedule;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class NotificationsController
 *
 * @package panlatent\schedule\controllers
 * @author Ryssbowh <boris@puzzlers.run>
 */
class NotificationsController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * @param int|null $scheduleId
     * @param int|null $notificationId
     * @param NotificationInterface|null $notification
     * @return Response
     */
    public function actionEditNotification(int $scheduleId = null, int $notificationId = null, NotificationInterface $notification = null): Response
    {
        $notifications = Plugin::$plugin->getNotifications();
        if ($notification === null) {
            $schedule = Plugin::$plugin->getSchedules()->getScheduleById($scheduleId);
            if (!$schedule) {
                throw new NotFoundHttpException();
            }

            if ($notificationId !== null) {
                $notification = $notifications->getNotificationById($notificationId);
                if (!$notification) {
                    throw new NotFoundHttpException();
                }
            }
        } else {
            $schedule = $notification->getSchedule();
        }

        if (!$schedule) {
            throw new NotFoundHttpException();
        }

        $registeredNotifications = $notifications->getRegisteredNotifications();

        $notificationTypes = [];
        foreach ($registeredNotifications as $class) {
            $notificationTypes[] = [
                'label' => $class->name,
                'value' => $class->handle,
            ];
        }

        $crumbs = [
            ['label' => Craft::t('schedule', 'Schedule'), 'url' => UrlHelper::cpUrl('schedule')],
        ];

        if ($schedule->group) {
            $crumbs[] = ['label' => (string)$schedule->group, 'url' => UrlHelper::cpUrl('schedule/groups/' . $schedule->group->id)];
        }
        $crumbs[] = ['label' => (string)$schedule, 'url' => UrlHelper::cpUrl('schedule/' . $schedule->id)];
        $crumbs[] = ['label' => Craft::t('schedule', 'Notifications'), 'url' => UrlHelper::cpUrl('schedule/'. $schedule->id . '/notifications')];

        return $this->renderTemplate('schedule/notifications/_edit', [
            'notification' => $notification,
            'notificationTypes' => $notificationTypes,
            'schedule' => $schedule,
            'title' => $notification ? $notification->name : Craft::t('schedule', 'Create a notification'),
            'crumbs' => $crumbs,
        ]);
    }

    /**
     * Save a notification
     * 
     * @return ?Response
     */
    public function actionSaveNotification(): ?Response
    {
        $this->requirePostRequest();

        $notifications = Plugin::$plugin->getNotifications();
        $request = Craft::$app->getRequest();

        $handle = $request->getBodyParam('handle');
        $id = $request->getBodyParam('id');

        $notification = $notifications->getRegisteredNotificationByHandle($handle);
        if ($id) {
            $_notification = $notifications->getNotificationById($id);
            if ($_notification->handle == $handle) {
                $notification = $_notification;
            } else {
                $notification->setAttributes($_notification->getAttributes(), false);
            }
        }

        $notification->enabled = $request->getBodyParam('enabled');
        $notification->settings = $request->getBodyParam('settings', []);
        $notification->scheduleId = $request->getBodyParam('scheduleId');

        if (!$notifications->saveNotification($notification)) {
            Craft::$app->getSession()->setError(Craft::t('schedule', 'Couldn’t save notification.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'notification' => $notification,
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('schedule', 'Notification saved.'));

        return $this->redirectToPostedUrl();
    }

    /**
     * Get settings for a notification handle or id
     * 
     * @return Response
     */
    public function actionSettings(): Response
    {
        $notifications = Plugin::$plugin->getNotifications();
        $handle = $this->request->getRequiredParam('handle');
        $id = $this->request->getParam('id');
        $notification = $notifications->getRegisteredNotificationByHandle($handle);
        if ($id) {
            $_notification = $notifications->getNotificationById($id);
            if ($_notification->handle == $handle) {
                $notification = $_notification;
            }
        }
        $settings = \Craft::$app->view->renderTemplate('schedule/notifications/settings', [
            'template' => $notification->getSettingsTemplate(),
            'notification' => $notification,
            'settings' => $notification->settingsInstance
        ], View::TEMPLATE_MODE_CP);
        return $this->asJson([
            'settings' => $settings,
            'headHtml' => \Craft::$app->view->getHeadHtml(),
            'footHtml' => \Craft::$app->view->getBodyHtml(),
        ]);
    }

    /**
     * Delete a notification.
     *
     * @return Response
     */
    public function actionDeleteNotification(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $notifications = Plugin::$plugin->getNotifications();

        $notificationId = $this->request->getRequiredBodyParam('id');
        $notification = $notifications->getNotificationById($notificationId);
        if (!$notification) {
            throw new NotFoundHttpException();
        }

        if (!$notifications->deleteNotification($notification)) {
            return $this->asJson([
                'success' => false,
            ]);
        }

        return $this->asJson([
            'success' => true,
        ]);
    }

    /**
     * Reorder all notifications.
     *
     * @return Response
     */
    public function actionReorderNotifications(): Response
    {
        $this->requirePostRequest();

        $ids = Craft::$app->getRequest()->getBodyParam('ids');
        $ids = Json::decodeIfJson($ids);

        return $this->asJson([
            'success' => Plugin::$plugin->getNotifications()->reorderNotifications($ids)
        ]);
    }
}