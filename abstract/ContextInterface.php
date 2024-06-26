<?php

namespace panlatent\craft\actions\abstract;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

interface ContextInterface
{
    public function addError(string $attribute, string $error): void;

    public function hasErrors(): bool;

    public function getContainer(): ContainerInterface;

    public function getErrors(): array;

    public function getLogger(): LoggerInterface;

    public function getInput(): InputInterface;

    public function getOutput(): OutputInterface;

    public function setOutput(OutputInterface $output): void;
}