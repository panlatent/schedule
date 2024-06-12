<?php

namespace panlatent\craft\actions\abstract;

use Psr\Log\LoggerInterface;

interface ContextInterface
{
    public function getErrors(): array;

    public function getLogger(): LoggerInterface;

    public function getInput(): InputInterface;

    public function getOutput(): OutputInterface;

    public function setOutput(OutputInterface $output): void;
}