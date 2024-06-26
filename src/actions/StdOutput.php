<?php

namespace panlatent\schedule\actions;

use Craft;
use panlatent\craft\actions\abstract\OutputInterface;
use yii\helpers\Console as ConsoleHelper;

class StdOutput implements OutputInterface
{
    private string $message = '';

    public function canStored(): bool
    {
        return true;
    }

    public function render(): string
    {
        return Craft::$app->getView()->renderTemplate('schedule/_components/actions/Console/output.twig', [
            'message' => $this->message,
        ]);
    }

    public function writeln(string $message): void
    {
        $message = ConsoleHelper::ansiToHtml($message);
        $this->message .= str_replace(PHP_EOL, '<br>', $message) . '<br>';
    }
}