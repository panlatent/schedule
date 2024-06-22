<?php

namespace panlatent\schedule\actions;

use Craft;
use panlatent\craft\actions\abstract\Action;
use panlatent\craft\actions\abstract\ContextInterface;
use panlatent\schedule\Plugin;
use Symfony\Component\Process\Process;

class Command extends Action
{
    public ?string $command = null;

    public ?string $arguments = null;

    public ?string $workDir = null;

    public ?array $env = null;

    public ?string $osUser = null;

    public ?int $timeout = null;

    /**
     * Build the command array.
     *
     * @return array
     */
    public function buildCommand(): array
    {
        $command = [
            $this->command,
            $this->arguments,
        ];

        if ($this->osUser) {
            $command = array_merge(['sudo -u', $this->osUser], $command);
        }

        return $command;
    }

    public function execute(ContextInterface $context): bool
    {
        $workDir = $this->workDir ?: dirname(Craft::$app->request->getScriptFile());
        $process = new Process($this->buildCommand(), $workDir, $this->env, null, $this->timeout ?: null);

        $process->run(function ($type, $buffer) use ($context) {
            $output = $buffer . "\n";
            $context->getLogger()->info($output);
        });

        return $process->isSuccessful();
    }

    public function getSettingsHtml(): ?string
    {
        $suggestions = [];

        $process = new Process([Plugin::getInstance()->getSettings()->getCliPath(), 'craft', 'help/list'], Craft::getAlias('@root'));
        $process->run();

        if ($process->isSuccessful()) {
            $lines = explode("\n", mb_convert_encoding($process->getOutput(), mb_internal_encoding()));

            $data = [];
            foreach ($lines as $line) {
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

        return Craft::$app->getView()->renderTemplate('schedule/_components/actions/Command/settings', [
            'schedule' => $this,
            'suggestions' => $suggestions,
        ]);
    }
}