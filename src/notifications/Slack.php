<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2019 panlatent@gmail.com
 */

namespace panlatent\schedule\notifications;

use craft\base\Model;
use craft\helpers\Json;
use panlatent\schedule\Plugin;
use panlatent\schedule\base\Notification;

/**
 * Class Email
 *
 * @package panlatent\schedule\services
 * @author Ryssbowh <boris@puzzlers.run>
 */
class Slack extends Notification
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return \Craft::t('schedule', 'Slack');
    }

    /**
     * @inheritDoc
     */
    public function getHandle(): string
    {
        return 'slack';
    }

    /**
     * @inheritDoc
     */
    public function getSettingsModel(): Model
    {
        return new SlackSettings;
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        $settings = $this->slackSettings;
        if (!$settings['apiToken']) {
            return \Craft::t('schedule', 'Disabled : No api key has been defined');
        }
        $description = 'Send message to channel {channel}';
        if ($this->settings['onError'] and $this->settings['onSuccess']) {
            $description .= ', on error and on success';
        } else if ($this->settings['onSuccess']) {
            $description .= ', on success';
        } else if ($this->settings['onError']) {
            $description .= ', on error';
        }
        return \Craft::t('schedule', $description, ['channel' => $settings['channel']]);
    }

    /**
     * @inheritDoc
     */
    public function notify(bool $success, ?array $log): bool
    {
        $settings = $this->slackSettings;
        if (!$settings['apiToken']) {
            \Craft::warning('Failed to send Slack notification, no api token was defined', __METHOD__);
            return true;
        }
        $res = true;
        if ($success and $this->settings['onSuccess']) {
            $res = $this->sendSuccessMessage($log, $settings);
        }
        if (!$success and $this->settings['onError']) {
            $res = ($res and $this->sendErrorMessage($log, $settings));
        }
        return $res;
    }

    /**
     * @inheritDoc
     */
    public function getSettingsTemplate(): string
    {
        return 'schedule/notifications/slack-settings';
    }

    /**
     * Send an error message to slack channel
     * 
     * @param  ?array  $log
     * @param  array   $settings
     * @return bool
     */
    protected function sendErrorMessage(?array $log, array $settings): bool
    {
        $message = \Craft::t('schedule', '{systemName} : Schedule {name} failed, no output was given', [
            'name' => $this->schedule->name,
            'systemName' => \Craft::$app->getSystemName()
        ]);
        if ($log['output'] ?? false) {
            $message = \Craft::t('schedule', "{systemName} : Schedule {name} failed, output was :\n```{output}```", [
                'name' => $this->schedule->name,
                'systemName' => \Craft::$app->getSystemName(),
                'output' => $log['output']
            ]);
        }
        return $this->sendMessage($message, $settings);
    }

    /**
     * Send a success message to slack channel
     * 
     * @param  ?array  $log
     * @param  array   $settings
     * @return bool
     */
    protected function sendSuccessMessage(?array $log, array $settings): bool
    {
        $message = \Craft::t('schedule', '{systemName} : Schedule {name} ran successfully, no output was given', [
            'systemName' => \Craft::$app->getSystemName(),
            'name' => $this->schedule->name
        ]);
        if ($log['output'] ?? false) {
            $message = \Craft::t('schedule', "{systemName} : Schedule {name} ran successfully, output was :\n```{output}```", [
                'name' => $this->schedule->name,
                'systemName' => \Craft::$app->getSystemName(),
                'output' => $log['output']
            ]);
        }
        return $this->sendMessage($message, $settings);
    }

    /**
     * Send a message to a slack channel
     * 
     * @param  string $message
     * @param  array  $settings
     * @return bool
     */
    protected function sendMessage(string $message, array $settings): bool
    {
        $ch = curl_init("https://slack.com/api/chat.postMessage");
        $data = http_build_query([
            "token" => $settings['apiToken'],
            "channel" => $settings['channel'],
            "text" => $message
        ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        curl_close($ch);
        $result = Json::decodeIfJson($output);
        if (!$result['ok'] ?? false) {
            \Craft::warning('Failed to send Slack notification : ' . $output, __METHOD__);
        }
        return $result['ok'] ?? false;
    }

    /**
     * Get the slack settings, either from the notification settings or the global slack settings
     * 
     * @return array
     */
    protected function getSlackSettings(): array
    {
        $settings = [
            'channel' => $this->settingsInstance->channel,
            'apiToken' => \Craft::parseEnv($this->settings['apiToken'])
        ];
        if ($this->settings['useGlobalToken']) {
            $settings['apiToken'] = \Craft::parseEnv(Plugin::getInstance()->settings->slackApiToken);
        }
        return $settings;
    }
}