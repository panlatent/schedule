<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\schedules;

use Craft;
use craft\helpers\Json;
use GuzzleHttp\Client;
use panlatent\schedule\base\Schedule;
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
    public $method = 'get';

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $contentType;

    /**
     * @var string
     */
    public $body;

    // Public Methods
    // =========================================================================

    /**
     * @return array
     */
    public function getMethods(): array
    {
        return [
            'get' => 'Get',
            'head' => 'Head',
            'post' => 'Post',
            'put' => 'Put',
            'patch' => 'Patch',
            'delete' => 'Delete',
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
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('schedule/_components/schedules/HttpRequest', [
            'schedule' => $this,
        ]);
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function execute(int $logId): bool
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

        /** @var ResponseInterface $response */
        $response = $client->{$this->method}($this->url, $options);
        $statusCode = $response->getStatusCode();

        $successful =  $statusCode >= 200 && $statusCode < 300;
        if ($successful) {
            Craft::info("Http Request Schedule: a http request has been sent to {$this->url}({$statusCode}) successfully.", __METHOD__);
        } else {
            Craft::warning("Http Request Schedule: a http request has been sent to {$this->url}({$statusCode}) failed.", __METHOD__);
        }

        return $successful;
    }
}