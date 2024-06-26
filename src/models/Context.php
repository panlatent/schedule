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
    private ?OutputInterface $output = null;

    private array $errors = [];

    public function __construct(protected readonly LoggerInterface $logger, protected readonly ?ContainerInterface $container = null)
    {

    }

    public function addError(string $attribute, string $error): void
    {
        $this->errors[$attribute] = $error;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container ?? new ContainerAdapter(new Container());
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function getInput(): InputInterface
    {

    }

    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

}