<?php

namespace panlatent\schedule\models;

use panlatent\craft\actions\abstract\ContextInterface;
use panlatent\craft\actions\abstract\InputInterface;
use panlatent\craft\actions\abstract\OutputInterface;
use panlatent\schedule\di\ContainerAdapter;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use yii\di\Container;

class Context implements ContextInterface
{
    public function __construct(protected readonly LoggerInterface $logger, protected readonly ?ContainerInterface $container = null)
    {

    }

    public function getContainer(): ContainerInterface
    {
        return $this->container ?? new ContainerAdapter(new Container());
    }

    public function getErrors(): array
    {
        return [];
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function getInput(): InputInterface
    {
        // TODO: Implement getInput() method.
    }

    public function getOutput(): OutputInterface
    {
        // TODO: Implement getOutput() method.
    }

    public function setOutput(OutputInterface $output): void
    {
        // TODO: Implement setOutput() method.
    }

}