<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\models;

use Craft;
use craft\base\Model;
use panlatent\schedule\validators\CarbonStringIntervalValidator;
use panlatent\schedule\validators\PhpBinaryValidator;

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
     * @var string|null
     */
    public $logExpireAfter;

    /**
     * @deprecated
     * @var bool
     */
    public $modifyPluginName = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['cliPath', 'customName', 'customCpNavName', 'logExpireAfter'], 'string'],
            [['modifyPluginName'], 'boolean'],
            [['cliPath'], PhpBinaryValidator::class, 'minVersion' => '7.1', 'allowParseEnv' => true],
            [['logExpireAfter'], CarbonStringIntervalValidator::class],
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
