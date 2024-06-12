<?php

namespace panlatent\schedule\models;

use panlatent\craft\actions\abstract\ContextInterface;
use panlatent\craft\actions\abstract\InputInterface;
use panlatent\craft\actions\abstract\OutputInterface;
use Psr\Log\LoggerInterface;
use yii\base\Component;

class Context extends Component implements ContextInterface
{
    public function __construct(protected LoggerInterface $logger, $config = [])
    {
        parent::__construct($config);
    }

    public function getErrors(): array
    {

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