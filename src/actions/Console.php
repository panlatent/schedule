<?php

namespace panlatent\schedule\actions;

use Craft;
use panlatent\craft\actions\abstract\Action;
use panlatent\craft\actions\abstract\ContextInterface;
use panlatent\schedule\Plugin;
use Symfony\Component\Process\Process;

class Console extends Command
{
    public function execute(ContextInterface $context): bool
    {
//        $process = new Process($this->buildCommand(), dirname(Craft::$app->request->getScriptFile()), null, null, $this->timeout ?: null);
//
//        $process->run(function ($type, $buffer) use ($logId) {
//            $output = $buffer . "\n";
//            Craft::$app->getDb()->createCommand()
//                ->update(Table::SCHEDULELOGS, [
//                    'status' => self::STATUS_PROCESSING,
//                    'output' => new Expression("CONCAT([[output]],:output)", ['output' => $output]),
//                ], [
//                    'id' => $logId,
//                ])
//                ->execute();
//        });
//
//        return $process->isSuccessful();
    }

    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('schedule/_components/actions/Console/settings', [
            'schedule' => $this,
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
                        'label' => $match[1] . ' - ' . $match[2], //substr($line, 0, $pos),
                        'value' => $match[1],
                    ];
                }
            }
        }

        return $options;
    }

}