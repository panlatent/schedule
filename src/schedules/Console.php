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
use panlatent\schedule\Builder;
use Symfony\Component\Process\Process;

/**
 * Class CommandSchedule
 *
 * @package panlatent\schedule\schedules
 * @author Panlatent <panlatent@gmail.com>
 */
class Console extends Schedule
{
    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('schedule', 'Console');
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
     * @inheritdoc
     */
    public function build(Builder $builder)
    {
        $builder->command($this->command . ' ' . $this->arguments)
            ->cron($this->getCronExpression())
            ->then(function() {
                $this->beforeRun();
            })
            ->on(\omnilight\scheduling\Event::EVENT_BEFORE_RUN, [$this, 'afterRun']);
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        $suggestions = [];

        $process = new Process(['craft', 'help/list'], Craft::getAlias('@root'));
        $process->run();

        if ($process->isSuccessful()) {
            $lines = (array)explode("\n", $process->getOutput());

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

        return Craft::$app->getView()->renderTemplate('schedule/_components/schedules/Console', [
            'schedule' => $this,
            'suggestions' => $suggestions,
        ]);
    }
}