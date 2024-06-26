<?php

namespace panlatent\schedule\actions;

use Craft;
use panlatent\schedule\Plugin;
use Symfony\Component\Process\Process;

class CraftConsole extends Console
{
    public const CRAFT_BIN = 'craft';

    public static function displayName(): string
    {
        return Craft::t('schedule', 'Craft Console');
    }

    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('schedule/_components/actions/CraftConsole/settings', [
            'action' => $this,
        ]);
    }

    public function getCommandOptions(): array
    {
        $options = [];

        $process = new Process([Plugin::getInstance()->getSettings()->getCliPath(), 'craft', 'help'], Craft::getAlias('@root'));
        $process->run();

        if ($process->isSuccessful()) {
            $lines = explode("\n", mb_convert_encoding($process->getOutput(), mb_internal_encoding()));
            foreach ($lines as $line) {
                if (str_starts_with($line, '-')) {
                    $options[] = ['optgroup' => substr($line, 2)];
                    continue;
                }
                if (preg_match('#^\s*(\w+/\w+)\s*(?:\(\w+\)|)\s+(.+)\s*$#', $line, $match)) {
                    $options[] = [
                        'label' => $match[1] . ' - ' . $match[2],
                        'value' => $match[1],
                    ];
                }
            }
        }

        return $options;
    }

    protected function buildCommand(): array
    {
        return array_merge([
            Plugin::getInstance()->getSettings()->getCliPath(),
            self::CRAFT_BIN,
        ], parent::buildCommand(), ['--color']);
    }

    protected function getWorkDir(): string
    {
        return Craft::getAlias('@root');
    }
}