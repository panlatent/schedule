<?php

namespace panlatent\schedule\log;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class MemoryLog implements LoggerInterface
{
    use LoggerTrait;

    private array $messages = [];

    public function log($level, $message, array $context = []): void
    {
        $this->messages[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context
        ];
    }

    public function getMessages(): array
    {
        return $this->messages;
    }
}