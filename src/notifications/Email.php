<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2019 panlatent@gmail.com
 */

namespace panlatent\schedule\notifications;

use craft\base\Model;
use craft\web\View;
use panlatent\schedule\base\Notification;

/**
 * Class Email
 *
 * @package panlatent\schedule\services
 * @author Ryssbowh <boris@puzzlers.run>
 */
class Email extends Notification
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return \Craft::t('schedule', 'Email');
    }

    /**
     * @inheritDoc
     */
    public function getHandle(): string
    {
        return 'email';
    }

    /**
     * @inheritDoc
     */
    public function getSettingsModel(): Model
    {
        return new EmailSettings;
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        if (!$this->settings['emails']) {
            return \Craft::t('schedule', 'Disabled : No emails defined');
        }
        $description = 'Send email to {number} addresses';
        if ($this->settings['onError'] and $this->settings['onSuccess']) {
            $description .= ', on error and on success';
        } else if ($this->settings['onSuccess']) {
            $description .= ', on success';
        } else if ($this->settings['onError']) {
            $description .= ', on error';
        }
        return \Craft::t('schedule', $description, ['number' => sizeof($this->settings['emails'])]);
    }

    /**
     * @inheritDoc
     */
    public function notify(bool $success, ?array $log): bool
    {
        if (!$this->settingsInstance->emails) {
            return true;
        }
        $res = true;
        if ($success and $this->settingsInstance->onSuccess) {
            $res = $this->sendSuccessEmail($log);
        }
        if (!$success and $this->settingsInstance->onError) {
            $res = ($res and $this->sendErrorEmail($log));
        }
        return $res;
    }

    /**
     * @inheritDoc
     */
    public function getSettingsTemplate(): string
    {
        return 'schedule/notifications/email-settings';
    }

    /**
     * Send an error email
     * 
     * @param  ?array  $log
     * @return bool
     */
    protected function sendErrorEmail(?array $log): bool
    {
        $subject = \Craft::t('schedule', '{systemName} : Schedule {name} failed', [
            'name' => $this->schedule->name,
            'systemName' => \Craft::$app->getSystemName()
        ]);
        return $this->sendEmail($subject, $log);
    }

    /**
     * Send a success email
     * 
     * @param  ?array  $log
     * @return bool
     */
    protected function sendSuccessEmail(?array $log): bool
    {
        $subject = \Craft::t('schedule', '{systemName} : Schedule {name} ran successfully', [
            'name' => $this->schedule->name,
            'systemName' => \Craft::$app->getSystemName()
        ]);
        return $this->sendEmail($subject, $log);
    }

    /**
     * Send an email
     * 
     * @param  string $subject
     * @param  ?array  $log
     * @return bool
     */
    protected function sendEmail(string $subject, ?array $log): bool
    {
        $content = \Craft::$app->view->renderTemplate('schedule/notifications/email', [
            'notification' => $this,
            'log' => $log
        ], View::TEMPLATE_MODE_CP);
        $emails = [];
        foreach ($this->settingsInstance->emails as $email) {
            if ($email['name']) {
                $emails[\Craft::parseEnv($email['email'])] = $email['name'];
            } else {
                $emails[] = \Craft::parseEnv($email['email']);
            }
        }
        $res = \Craft::$app->mailer
            ->compose()
            ->setTo($emails)
            ->setHtmlBody($content)
            ->setSubject($subject)
            ->send();
        if ($res) {
            \Craft::warning('Failed to send notification email', __METHOD__);
        }
        return $res;
    }
}