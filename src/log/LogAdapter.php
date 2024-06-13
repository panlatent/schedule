<?php

namespace panlatent\schedule\log;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;
use yii\log\Logger as YiiLogger;

class LogAdapter implements LoggerInterface
{
    use LoggerTrait;

    public function __construct(private readonly YiiLogger $logger, private readonly string $category)
    {
    }

    public function log($level, $message, array $context = []): void
    {
        $this->logger->log((string) $message, match($level) {
            LogLevel::ERROR, LogLevel::ALERT, LogLevel::EMERGENCY, LogLevel::CRITICAL => YiiLogger::LEVEL_ERROR,
            LogLevel::NOTICE, LogLevel::WARNING => YiiLogger::LEVEL_WARNING,
            LogLevel::DEBUG, LogLevel::INFO => YiiLogger::LEVEL_INFO,
            default => throw new InvalidArgumentException("Unknown logging level $level")
        }, $this->category);
    }
}