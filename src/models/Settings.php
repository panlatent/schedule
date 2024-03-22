<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\models;

use craft\base\Model;
use craft\helpers\App;
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
    public string $cliPath = 'php';

    /**
     * @var string|null
     */
    public ?string $customName = null;

    /**
     * @var string|null
     */
    public ?string $customCpNavName = null;

    /**
     * @deprecated
     * @var bool
     */
    public bool $modifyPluginName = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['cliPath', 'customName', 'customCpNavName'], 'string'],
            [['modifyPluginName'], 'boolean'],
            [['cliPath'], PhpBinaryValidator::class, 'minVersion' => '7.1', 'allowParseEnv' => true],
        ];
    }

    /**
     * @return string
     */
    public function getCliPath(): string
    {
        return App::parseEnv($this->cliPath) ?: 'php';
    }
}