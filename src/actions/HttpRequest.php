<?php

namespace panlatent\schedule\actions;

use Alexanderpas\Common\HTTP\Method;
use Craft;
use craft\helpers\Json;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use panlatent\craft\actions\abstract\Action;
use panlatent\craft\actions\abstract\ActionInterface;
use panlatent\craft\actions\abstract\ArrayOutput;
use panlatent\craft\actions\abstract\ContextInterface;
use panlatent\craft\actions\abstract\OutputInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;

class HttpRequest extends Action
{
    public string $method = Method::GET->value;

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
    public array $queryParams = [];

    /**
     * @var string
     */
    public string $contentType = '';

    /**
     * @var string
     */
    public string $body = '';

    public function execute(ContextInterface $context): bool
    {
        try {
            $client = $context->getContainer()->get(ClientInterface::class);
        } catch (NotFoundExceptionInterface) {
            $client = new Client();
        }
        return $this->executeWithClient($context, $client);
    }

    public function executeWithClient(ContextInterface $context, ClientInterface $client): bool
    {
        $response = $client->request($this->method, $this->url, $this->getOptions());
        $statusCode = $response->getStatusCode();
        $context->setOutput(new ArrayOutput([
            'method' => $this->method,
            'url' => $this->url,
            'statusCode' => $statusCode,
            'headers' => $response->getHeaders(),
            'body' => $response->getBody()->getContents(),
        ], 'schedule/_components/actions/HttpRequest/output'));

        return $statusCode >= 200 && $statusCode < 400;
    }

    public function getMethods(): array
    {
        return [
            Method::GET->value,
            Method::HEAD->value,
            Method::POST->value,
            Method::PUT->value,
            Method::PATCH->value,
            Method::OPTIONS->value,
        ];
    }

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

    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('schedule/_components/actions/HttpRequest/settings', [
            'action' => $this,
            // Craft editableTable not support custom suggestions
            // 'httpHeaderSuggestions' => [
            //     [
            //          'label' => '',
            //         'data' => $this->_getHttpHeaderSuggestions(),
            //     ]
            // ],
        ]);
    }

    protected function getOptions(): array
    {
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

        foreach ($this->headers as ['name' => $name, 'value' => $value, 'enabled' => $enabled]) {
            if ($enabled) {
                $options['headers'][$name] = $value;
            }
        }

        foreach ($this->queryParams as ['name' => $name, 'value' => $value, 'enabled' => $enabled]) {
            if ($enabled) {
                $options['query'][$name] = $value;
            }
        }

        return $options;
    }
}