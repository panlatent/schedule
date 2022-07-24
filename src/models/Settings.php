<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\models;

use Craft;
use panlatent\schedule\validators\PhpBinaryValidator;
use yii\base\Model;

/**
 * Class Settings
 *
 * @package panlatent\schedule\models
 * @author Panlatent <panlatent@gmail.com>
 */
class Settings extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var string PHP binary path.
     */
    public $cliPath = 'php';

    /**
     * @var string|null
     */
    public $customName;

    /**
     * @var string|null
     */
    public $customCpNavName;

    /**
     * @deprecated
     * @var bool
     */
    public $modifyPluginName = false;

    /**
     * @var boolean
     */
    public $disableNotifications = false;

    /**
     * @var string
     */
    public $slackApiToken;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cliPath', 'customName', 'customCpNavName', 'slackApiToken'], 'string'],
            [['modifyPluginName', 'disableNotifications'], 'boolean'],
            [['cliPath'], PhpBinaryValidator::class, 'minVersion' => '7.1', 'allowParseEnv' => true],
        ];
    }

    /**
     * @return string
     */
    public function getCliPath(): string
    {
        return Craft::parseEnv($this->cliPath) ?: 'php';
    }
}