<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2019 panlatent@gmail.com
 */

namespace panlatent\schedule\notifications;

use craft\base\Model;

/**
 * Class EmailSettings
 *
 * @package panlatent\schedule\services
 * @author Ryssbowh <boris@puzzlers.run>
 */
class SlackSettings extends Model
{
    /**
     * @var boolean
     */
    public $useGlobalToken = false;

    /**
     * @var boolean
     */
    public $onSuccess;

    /**
     * @var boolean
     */
    public $onError;

    /**
     * @var string
     */
    public $apiToken;

    /**
     * @var string
     */
    public $channel;

    /**
     * @inheritDoc
     */
    public function defineRules(): array
    {
        return [
            [['useGlobalToken', 'onSuccess', 'onError'], 'boolean'],
            [['apiToken', 'channel'], 'string'],
            ['channel', 'required'],
            [['apiToken'], 'required', 'when' => function ($model) {
                return !$model->useGlobalToken;
            }]
        ];
    }
}