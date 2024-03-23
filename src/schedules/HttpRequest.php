<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\schedules;

use Craft;
use craft\helpers\Json;
use GuzzleHttp\Client;
use panlatent\schedule\base\Schedule;
use panlatent\schedule\db\Table;
use panlatent\schedule\models\ScheduleLog;
use Psr\Http\Message\ResponseInterface;

/**
 * Class HttpRequest
 *
 * @package panlatent\schedule\schedules
 * @author Panlatent <panlatent@gmail.com>
 */
class HttpRequest extends Schedule
{
    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('schedule', 'HTTP Request');
    }

    /**
     * @inheritdoc
     */
    public static function isRunnable(): bool
    {
        return true;
    }

    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public string $method = 'get';

    /**
     * @var string Request URL default is echo api.
     */
    public string $url = 'https://postman-echo.com/get';

    /**
     * @var array
     */
    public array $headers = [];

    /**
     * @var array
     */
    public array $urlParams = [];

    /**
     * @var string
     */
    public string $contentType = '';

    /**
     * @var string
     */
    public string $body = '';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['method', 'url'], 'required'],
            [['method', 'url'], 'string'],
            [['headers', 'urlParams'], function($property) {
                if (empty($this->$property)) {
                    return;
                }
                $hasErrors = false;
                foreach ($this->$property as $key => $row) {
                    if ($row['enabled'] && empty($row['name'])) {
                        $this->$property[$key]['name'] = [
                            'hasErrors' => true,
                            'value' => $row['name'],
                        ];
                        if (!$hasErrors) {
                            $this->addError($property, Craft::t('schedule', '{label} can not blank', [
                                'label' => $this->getAttributeLabel($property),
                            ]));
                            $hasErrors = true;
                        }
                    }
                }
            }]
        ]);
    }

    /**
     * @return array
     */
    public function getMethods(): array
    {
        return [
            'get' => 'GET',
            'head' => 'HEAD',
            'post' => 'POST',
            'put' => 'PUT',
            'patch' => 'PATCH',
            'delete' => 'DELETE',
        ];
    }

    /**
     * @return array
     */
    public function getContentTypes(): array
    {
        return [
            'multipart/form-data',
            'application/x-www-form-urlencoded',
            'application/json',
            'application/xml',
            'text/html',
            'text/xml',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('schedule/_components/schedules/HttpRequest/settings', [
            'schedule' => $this,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function renderLogContent(ScheduleLog $log): string
    {
        return Craft::$app->getView()->renderTemplate('schedule/_components/schedules/HttpRequest/log', [
            'log' => $log,
        ]);
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function execute(int $logId = null): bool
    {
        $client = new Client();

        $options = [];

        if (!in_array($this->method, ['get', 'head']) && !empty($this->body)) {
            switch ($this->contentType) {
                case 'application/json':
                    $options['json'] = Json::decode($this->body);
                    break;
                case 'application/x-www-form-urlencoded':
                    parse_str($this->body, $formParams);
                    $options['form_params'] = $formParams;
                    break;
                default:
                    $options['headers'] = ['content-type' => $this->contentType];
                    $options['body'] = $this->body;
            }
        }

        if (!empty($this->headers)) {
            foreach ($this->headers as ['name' => $name, 'value' => $value, 'enabled' => $enabled]) {
                if ($enabled) {
                    $options['headers'][$name] = $value;
                }
            }
        }

        if (!empty($this->urlParams)) {
            foreach ($this->urlParams as ['name' => $name, 'value' => $value, 'enabled' => $enabled]) {
                if ($enabled) {
                    $options['query'][$name] = $value;
                }
            }
        }

        /** @var ResponseInterface $response */
        $response = $client->{$this->method}($this->url, $options);
        $statusCode = $response->getStatusCode();

        $successful = $statusCode >= 200 && $statusCode < 300;
        if ($successful) {
            Craft::info("Http Request Schedule: a http request has been sent to $this->url($statusCode) successfully.", __METHOD__);
        } else {
            Craft::warning("Http Request Schedule: a http request has been sent to $this->url($statusCode) failed.", __METHOD__);
        }

        if ($logId) {
            Craft::$app->getDb()->createCommand()
                ->update(Table::SCHEDULELOGS, [
                    'output' => (string)$response->getBody(),
                ], [
                    'id' => $logId,
                ])
                ->execute();
        }

        return $successful;
    }
}