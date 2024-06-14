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

        return Craft::$app->getView()->renderTemplate('schedule/_components/schedules/Console/settings', [
            'schedule' => $this,
            'suggestions' => $suggestions,
        ]);
    }
}