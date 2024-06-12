<?php

namespace panlatent\schedule\actions;

use panlatent\craft\actions\abstract\Action;
use panlatent\craft\actions\abstract\ContextInterface;

class Console extends Command
{
    public function execute(ContextInterface $context): bool
    {
        $process = new Process($this->buildCommand(), dirname(Craft::$app->request->getScriptFile()), null, null, $this->timeout ?: null);

        $process->run(function ($type, $buffer) use ($logId) {
            $output = $buffer . "\n";
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