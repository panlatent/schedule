<?php

namespace panlatent\schedule\actions;

use Craft;
use craft\helpers\App;
use panlatent\craft\actions\abstract\Action;
use panlatent\craft\actions\abstract\ContextInterface;
use panlatent\schedule\Plugin;
use Symfony\Component\Process\Process;

class Console extends Action
{
    public ?string $command = null;

    public ?string $arguments = null;

    public ?string $workDir = null;

    public array $variables = [];

    public ?string $osUser = null;

    public ?int $timeout = null;

    public bool $disableOutput = false;

//    public bool $isPty = true;
//
//    public bool $isTty = false;

    public function execute(ContextInterface $context): bool
    {
        $output = new StdOutput();
        $context->setOutput($output);

        $process = $this->createProcess();

        $output = new StdOutput();
        $context->setOutput($output);

        $context->getLogger()->debug("Running command: \"{$process->getCommandLine()}\" in {$this->getWorkDir()}");
        $process->run(function ($type, $buffer) use ($output) {
            $type = Process::ERR === $type ? 'error' : 'info';
            $output->writeln("<$type>$buffer</$type>");
        });

        if (!$process->isSuccessful()) {
            $context->addError('exitCode', $process->getExitCodeText());
            $context->addError('output', $process->getErrorOutput());
        }

        return !$context->hasErrors();
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

        return Craft::$app->getView()->renderTemplate('schedule/_components/actions/Console/settings', [
            'action' => $this,
            'suggestions' => $suggestions,
        ]);
    }

    protected function createProcess(): Process
    {
        $command = $this->buildCommand();
        if ($this->osUser) {
            $command = array_merge(['sudo -u', $this->osUser], $command);
        }

        $process = new Process($command, $this->getWorkDir(), $this->getEnvironmentVariables(), null, $this->timeout ?: null);
//        $process->setTty($this->isTty);
//        $process->setPty($this->isPty);

        if ($this->disableOutput) {
            $process->disableOutput();
        }

        return $process;
    }

    protected function buildCommand(): array
    {
        $command = [$this->command];
        if ($this->arguments) {
            $command[] = $this->arguments;
        }
        return $command;
    }

    protected function defineRules(): array
    {
        return [
            [['command'], 'required'],
        ];
    }

    protected function getWorkDir(): string
    {
        return $this->workDir ?: dirname(Craft::$app->request->getScriptFile());
    }

    private function getEnvironmentVariables(): array
    {
        $env = [];
        foreach ($this->variables as $key => $value) {
            $env[$key] = App::parseEnv($value);
        }
        return $env;
    }
}