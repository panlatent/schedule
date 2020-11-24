<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\schedules;

use Craft;
use panlatent\schedule\base\Schedule;
use panlatent\schedule\db\Table;
use panlatent\schedule\models\ScheduleLog;
use panlatent\schedule\Plugin;
use Symfony\Component\Process\Process;
use yii\db\Expression;

/**
 * Class CommandSchedule
 *
 * @package panlatent\schedule\schedules
 * @author Panlatent <panlatent@gmail.com>
 */
class Console extends Schedule
{
    // Constants
    // =========================================================================

    const CRAFT_CLI_SCRIPT = 'craft';

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('schedule', 'Console');
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
     * @var string|null
     */
    public $command;

    /**
     * @var string|null
     */
    public $arguments;

    // Public Methods
    // =========================================================================

    /**
     * Build the command array.
     *
     * @return array
     */
    public function buildCommand(): array
    {
        $command = [
            PHP_BINARY,
            self::CRAFT_CLI_SCRIPT,
            $this->command,
            $this->arguments,
        ];

        if ($this->user) {
            $command = array_merge(['sudo -u', $this->user], $command);
        }

        return $command;
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        $suggestions = [];

        $process = new Process([Plugin::getInstance()->getSettings()->getCliPath(), 'craft', 'help/list'], Craft::getAlias('@root'));
        $process->run();

        if ($process->isSuccessful()) {
            $lines = (array)explode("\n", mb_convert_encoding($process->getOutput(), mb_internal_encoding()));

            $data = [];
            foreach ($lines as $index => $line) {
                if (($pos = strpos($line, '/')) === false) {
                    $data[$line] = [];
                    continue;
                }

                $data[substr($line, 0, $pos)][] = [
                    'name' => $line,
                    'hint' => $line,
                ];
            }

            foreach ($data as $label => $commandSuggestions) {
                $suggestions[] = [
                    'label' => $label,
                    'data' => $commandSuggestions,
                ];
            }
        }

        return Craft::$app->getView()->renderTemplate('schedule/_components/schedules/Console/settings', [
            'schedule' => $this,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function renderLogContent(ScheduleLog $log): string
    {
        $content = str_replace("\n", '<br>', $log->output);

        return Craft::$app->getView()->renderTemplate('schedule/_components/schedules/Console/log', [
            'content' => $content,
        ]);
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function execute(int $logId = null): bool
    {
        $process = new Process($this->buildCommand(), dirname(Craft::$app->request->getScriptFile()), null, null, null);

        $process->run(function ($type, $buffer) use ($logId) {

            if (Process::ERR === $type) {
                $output = $buffer . "\n";
            } else {
                $output = $buffer . "\n";;
            }

            Craft::$app->getDb()->createCommand()
                ->update(Table::SCHEDULELOGS, [
                    'status' => self::STATUS_PROCESSING,
                    'output' => new Expression("CONCAT([[output]],:output)", ['output' => $output]),
                ], [
                    'id' => $logId,
                ])
                ->execute();
        });

        return $process->isSuccessful();
    }
}
