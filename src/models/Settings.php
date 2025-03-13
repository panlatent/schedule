<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\models;

use craft\base\Model;
use craft\helpers\App;
use panlatent\schedule\builder\Buidler as ScheduleBuilder;
use panlatent\schedule\validators\CarbonStringIntervalValidator;
use panlatent\schedule\validators\PhpBinaryValidator;
use yii\base\InvalidConfigException;

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
     * @deprecated
     * @var string|null
     */
    public ?string $customName = null;

    /**
     * @var string|null
     */
    public ?string $customCpNavName = null;

    // Web Cron
    // =========================================================================

    public bool $enabledWebCron = false;

    public ?string $endpoint = null;

    public ?array  $allowMethods = null;

    public ?array  $allowUserAgents = null;

    public ?string $token = null;

    // Logs
    // =========================================================================

    /**
     * @var string|null
     */
    public ?string $logExpireAfter = null;

    /**
     * Static schedules
     * @var array
     */
    public array $schedules = [];

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['cliPath', 'customCpNavName', 'logExpireAfter'], 'string'],
            [['customCpNavName'], 'trim'],
            [['cliPath'], PhpBinaryValidator::class, 'minVersion' => '7.1', 'allowParseEnv' => true],
            [['logExpireAfter'], CarbonStringIntervalValidator::class],
        ];
    }

    /**
     * @return string
     */
    public function getCliPath(): string
    {
        return App::parseEnv($this->cliPath) ?: 'php';
    }

    /**
     * @return string|null
     */
    public function getCustomCpNavName(): ?string
    {
        return App::parseEnv($this->customCpNavName);
    }

    public function getSchedules(): array
    {
        return array_map(function($item) {
            if ($item instanceof Schedule) {
                return $item;
            }

            if ($item instanceof ScheduleBuilder) {
                return $item->create();
            }

            throw new InvalidConfigException();
        }, $this->schedules);
    }
}
